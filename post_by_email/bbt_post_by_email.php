<?php
/**
 * Post By Email
 *
 */
class BBT_Post_By_Email {
    /**
     * Instance of this class.
     *
     * @since    0.9.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Default settings.
     *
     * @since    0.9.8
     *
     * @var      array
     */
    public static $default_options = array(
        'mailserver_url'            => 'mail.example.com',
        'mailserver_login'          => 'login@example.com',
        'mailserver_pass'           => '',
        'mailserver_protocol'       => 'IMAP',
        'mailserver_port'           => 993,
        'ssl'                       => true,
        'default_email_category'    => '',
        'delete_messages'           => true,
        'status'                    => 'configured',
        'pin_required'              => false,
        'pin'                       => '',
        'discard_pending'           => true,
        'interval'                  => 3600 //one hour default
    );

    /**
     * Active connection.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected $connection;

    /**
     * Connection protocol (POP3 or IMAP).
     *
     * @since    1.0.1
     *
     * @var      string
     */
    protected $protocol;

    /**
     * Initialize the plugin by setting localization, filters, and administration functions.
     *
     * @since     0.9.0
     */
    private function __construct() {

        if(!file_exists(get_template_directory() . '/theme_config/post_email_config.php')) return;

        require get_template_directory() . '/theme_config/post_email_config.php';

        if(!(!empty($config) && isset($config['enabled_post_email']) && $config['enabled_post_email'] == 'on')) return;

        add_filter( 'cron_schedules', array($this, 'isa_add_cron_recurrence_interval'));

        $this->bbt_email_activate($config);

        // Enable autoloading
        self::load();

        // add hooks to check for mail
        add_action( 'bbt_custom_interval_hook', array( $this, 'hook_check_email' ) );
        //add_action( 'bbt_custom_interval_hook', array( $this, 'check_email' ) );

        add_action('init', array($this, 'maybe_check_mail'));
    }

    /**
     * Return an instance of this class.
     *
     * @since     0.9.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Initiate manual check for new mail.
     *
     * @since    1.0.3
     */
    public function maybe_check_mail() {
        if ( isset( $_GET['bbt_manual_check_mail_test_config'] ) ) {
            $this->hook_check_email();
        }
    }

    /**
     * Generate a good PIN.
     *
     * @since    1.0.2
     */
    public function generate_pin() {
        /*check_ajax_referer( 'post-by-email-generate-pin', 'security' );
        if ( current_user_can( 'manage_options' ) ) {
            echo wp_generate_password( 8, true, false );
        }

        die();*/
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    0.9.0
     *
     */
    public static function bbt_email_activate($plugin_options) {
        $options = self::$default_options;

        // if old global options exist, copy them into plugin options
        foreach ( array_keys( self::$default_options ) as $optname ) {
            if ( isset( $plugin_options[$optname] ) ) {
                $options[$optname] = $plugin_options[$optname];
            } elseif ( get_option( $optname ) ) {
                $options[ $optname ] = get_option( $optname );
            }
        }

        if ( ! isset( $plugin_options['mailserver_protocol'] )
            && in_array( $options['mailserver_port'], array( 110, 995 ) ) ) {
            $options['mailserver_protocol'] = 'POP3';
            $options['delete_messages'] = false;
        }

        if ( ! isset( $plugin_options['ssl'] )
            && in_array( $options['mailserver_port'], array( 110, 143 ) ) ) {
            $options['ssl'] = false;
        }

        update_option( 'bbt_post_by_email_options', $options );

        // if log already exists, this will return false, and that is okay
        //add_option( 'post_by_email_log', array(), '', 'no' );

        if (empty($options['interval'])) {
            $options['interval'] = 'hourly';
        }

        if ($options['interval'] != wp_get_schedule('bbt_custom_interval_hook')) {
            wp_clear_scheduled_hook( 'bbt_custom_interval_hook' );
            //try to create the new schedule with the first run in 5 minutes
            if (false === wp_schedule_event(time() + 5 * 60, $options['interval'], 'bbt_custom_interval_hook')) {
                //error_log("postie_cron: Failed to set up cron task: $interval");
            } else {
                wp_schedule_event(time() + 5 * 60, $options['interval'], 'bbt_custom_interval_hook');
            }
        }

        // schedule custom_shedule mail checks with wp_cron --- for test
        /*if ( ! wp_next_scheduled( 'bbt_custom_interval_hook' ) ) {
            wp_schedule_event( current_time( 'timestamp', 1 ), 'custom_interval', 'bbt_custom_interval_hook' );
        }*/
    }

    /**
     * Run "check_email" when not called by wp_cron.
     *
     * @since    0.9.9
     */
    public function hook_check_email() {
        $this->check_email();
    }

    /**
     * Check for new messages and post them to the blog.
     *
     * @since    0.9.0
     */
    public function check_email() {
        // Only check at this interval for new messages.
        if ( ! defined( 'WP_MAIL_INTERVAL' ) )
            define( 'WP_MAIL_INTERVAL', 5 * MINUTE_IN_SECONDS );

        $last_checked = get_transient( 'mailserver_last_checked' );

        if ( $last_checked && ! WP_DEBUG ) {
            $time_diff = esc_html__( human_time_diff( time(), time() + WP_MAIL_INTERVAL ), 'BigBangThemesFramework' );
            $log_message = sprintf( esc_html__( 'Please wait %s to check mail again!', 'BigBangThemesFramework' ), $time_diff );
            //$this->save_log_message( $log_message );
            return;
        }

        set_transient( 'mailserver_last_checked', true, WP_MAIL_INTERVAL );

        $options = get_option( 'bbt_post_by_email_options' );

        // if options aren't set, there's nothing to do, move along
        if ( 'unconfigured' == $options['status'] ) {
            return;
        }
        $options['last_checked'] = current_time( 'timestamp' );
        $options['status'] = '';
        update_option( 'bbt_post_by_email_options', $options );

        $connection_options = array(
            'username' => $options['mailserver_login'],
            'password' => $options['mailserver_pass'],
            'hostspec' => $options['mailserver_url'],
            'port' => $options['mailserver_port'],
            'secure' => $options['ssl'] ? 'ssl' : false,
        );

        $this->connection = $this->open_mailbox_connection( $connection_options );
        if ( ! $this->connection ) {
            return;
        }

        $uids = $this->get_messages();
        if ( 0 === sizeof( $uids ) ) {
            //$this->save_log_message( esc_html__( 'There doesn&#8217;t seem to be any new mail.', 'BigBangThemesFramework' ) );
            $this->close_connection();
            return;
        }

        $log_message = sprintf( _n( 'Found 1 new message.', 'Found %s new messages.', sizeof( $uids ), 'BigBangThemesFramework' ), sizeof( $uids ) );

        $time_difference = get_option( 'gmt_offset' ) * HOUR_IN_SECONDS;
        $phone_delim = '::';

        foreach ( $uids as $id ) {
            $uid = new Horde_Imap_Client_Ids( $id );

            // get headers
            $headers = $this->get_message_headers( $uid );

            /* Subject */
            // Captures any text in the subject before $phone_delim as the subject
            $subject = $headers->getValue( 'Subject' );
            $subject = explode( $phone_delim, $subject );
            $subject = $subject[0];
            $subject = trim( $subject );


            /* Author */
            $from_email = $this->get_message_author( $headers );

            $userdata = get_user_by( 'email', $from_email );
            if ( ! empty( $userdata ) ) {
                $post_author = $userdata->ID;

                // Set $post_status based on author's publish_posts capability
                $user = new WP_User( $post_author );
                $post_status = ( $user->has_cap( 'publish_posts' ) ) ? 'publish' : 'pending';
            } else {
                if ( $options['discard_pending'] ) {
                    // $log_message .= '<br />' . sprintf( esc_html__( "No author match for %s (Subject: %s); skipping.", 'BigBangThemesFramework' ), $from_email, $subject );
                    continue;
                }
                // use admin if no author found
                $post_author = $this->get_admin_id();
                $post_status = 'pending';
            }


            /* Date */
            $ddate_U = $this->get_message_date( $headers );
            $post_date = gmdate( 'Y-m-d H:i:s', $ddate_U + $time_difference );
            $post_date_gmt = gmdate( 'Y-m-d H:i:s', $ddate_U );


            /* Message body */
            $content = $this->get_message_body( $uid );

            //Give Post-By-Email extending plugins full access to the content
            //Either the raw content or the content of the last quoted-printable section
            $content = apply_filters( 'wp_mail_original_content', $content );

            // Captures any text in the body after $phone_delim as the body
            $content = explode( $phone_delim, $content );
            $content = empty( $content[1] ) ? $content[0] : $content[1];

            $content = trim( $content );

            // replace HTML-ized quotes with the real thing, so shortcode arguments work
            $content = str_replace( array( '&#39;', '&quot;' ), array( "'", '"' ), $content );

            $post_content = apply_filters( 'phone_content' , $content );

            /* post title */
            $post_title = xmlrpc_getposttitle( $content );

            if ( '' == $post_title )
                $post_title = $subject;

            /* validate PIN */
            if ( $options['pin_required'] ) {
                $pin = $this->find_shortcode( 'pin', $post_content );
                $pin = implode( $pin );

                if ( $pin != $options['pin'] ) {
                    // security check failed - move on to the next message
                    //$log_message .= '<br />"' . $post_title . '" ' . esc_html__( 'failed PIN authentication; discarding.', 'BigBangThemesFramework' );
                    continue;
                }
            }

            /* shortcode: tags. [tag tag1 tag2...] */

            $tags_input = $this->find_shortcode( 'tag', $post_content );
            $tax_input = $tags_input;

            $original_post_content = $post_content;
            $post_content = $this->filter_valid_shortcodes( $post_content );

            if ( function_exists( 'is_bbpress' ) ) {
                $post_type = bbp_get_topic_post_type();
                $post_parent = $options['default_email_category'];
                $comment_status = 'closed';

                /* create the post */
                $post_data = compact( 'post_type','post_content', 'post_title', 'post_date', 'post_date_gmt', 'post_author', 'post_parent', /*'post_category',*/ 'post_status', 'comment_status', 'tax_input' /*tags_input*/ );
            }
            else {
                /* shortcode: categories. [category cat1 cat2...] */
                $shortcode_categories = $this->find_shortcode( 'category', $post_content );
                $post_category = array();
                if ( empty( $shortcode_categories ) ) {
                    $post_category[] = $options['default_email_category'];
                }
                foreach ( $shortcode_categories as $cat ) {
                    if ( is_numeric( $cat ) ) {
                        $post_category[] = $cat;
                    } elseif ( get_category_by_slug( $cat ) ) {
                        $term = get_category_by_slug( $cat );
                        $post_category[] = $term->term_id;
                    } else {  // create new category
                        $new_category = wp_insert_term( $cat, 'category' );
                        if ( $new_category ) {
                            $post_category[] = $new_category['term_id'];
                        }
                    }
                }

                $post_type = 'post';

                $post_data = compact( 'post_type', 'post_content', 'post_title', 'post_date', 'post_date_gmt', 'post_author', 'post_category', 'post_status', tags_input );
            }

            $post_data = wp_slash( $post_data );

            $post_ID = wp_insert_post( $post_data );
            if ( is_wp_error( $post_ID ) ) {
                //$log_message .= "\n" . $post_ID->get_error_message();
                //$this->save_error_message( $log_message );
            }

            // We couldn't post, for whatever reason. Better move forward to the next email.
            if ( empty( $post_ID ) )
                continue;

            if ( function_exists( 'is_bbpress' ) ) {
                bbp_update_topic( $post_ID, $post_parent );
            }

            // save original message sender as post_meta, in case we want it later
            add_post_meta( $post_ID, 'original_author', $from_email );

            /* shortcode: custom taxonomies.  [taxname term1 term2 ...] */
            $tax_input = array();

            // get all registered custom taxonomies
            $args = array(
                'public'   => true,
                '_builtin' => false,
            );
            $registered_taxonomies = get_taxonomies( $args, 'names', 'and' );

            if ( $registered_taxonomies ) {
                foreach ( $registered_taxonomies as $taxonomy_name ) {
                    $tax_shortcodes = $this->find_shortcode( $taxonomy_name, $original_post_content );
                    if ( count( $tax_shortcodes ) > 0 ) {
                        // pending bug fix: http://core.trac.wordpress.org/ticket/19373
                        //$tax_input[] = array( $taxonomy_name => $tax_shortcodes );
                        wp_set_post_terms( $post_ID, $tax_shortcodes, $taxonomy_name );
                    }
                }
            }

            /* attachments */
            $attachment_ids = $this->save_attachments( $uid, $post_ID );
            if ( count($attachment_ids) > 0 && ! has_shortcode( $post_content, 'gallery' ) ) {

                // add a default gallery if there isn't one already
                $post_info = array(
                    'ID' => $post_ID,
                    'post_content' => $post_content //. '[gallery ids="'.implode(',', $attachment_ids).'"]',
                );

                wp_update_post( $post_info );
            }

            do_action( 'publish_phone', $post_ID );

            /*if ( '' == $post_title ) {
                $post_title = esc_html__( '(no title)', 'BigBangThemesFramework' );
            }

            $pending = '';
            if ( 'pending' == $post_status ) {
                $pending = esc_html__( ' (pending)', 'BigBangThemesFramework' );
            }*/

            //$log_message .= "<br />" . esc_html__( 'Posted:', 'BigBangThemesFramework') . ' <a href="' . get_permalink( $post_ID ) . '">' . esc_html( $post_title ) . '</a>' . $pending;

        } // end foreach

        //$this->save_log_message( $log_message );

        // mark all processed emails as read
        $this->mark_as_read( $uids, $options['delete_messages'] );

        $this->close_connection();
    }

    public function isa_add_cron_recurrence_interval( $schedules ) {
        //Do not echo output in filters, it seems to break some installs
        //error_log("cron_schedules_filter: setting cron schedules");
        $schedules['weekly'] = array('interval' => (60 * 60 * 24 * 7), 'display' => esc_html__('Once Weekly', 'BigBangThemesFramework'));
        $schedules['twohours'] = array('interval' => 60 * 60 * 2, 'display' => esc_html__('Every 2 hours', 'BigBangThemesFramework'));
        $schedules['twiceperhour'] = array('interval' => 60 * 30, 'display' => esc_html__('Twice per hour', 'BigBangThemesFramework'));
        $schedules['tenminutes'] = array('interval' => 60 * 10, 'display' => esc_html__('Every 10 minutes', 'BigBangThemesFramework'));
        $schedules['fiveminutes'] = array('interval' => 60 * 5, 'display' => esc_html__('Every 5 minutes', 'BigBangThemesFramework'));
        $schedules['oneminute'] = array('interval' => 60 * 1, 'display' => esc_html__('Every 1 minute', 'BigBangThemesFramework'));
        $schedules['thirtyseconds'] = array('interval' => 30, 'display' => esc_html__('Every 30 seconds', 'BigBangThemesFramework'));
        $schedules['fifteenseconds'] = array('interval' => 15, 'display' => esc_html__('Every 15 seconds', 'BigBangThemesFramework'));

        return $schedules;
    }

    /**
     * Returns the site administrator's ID (used to set the author of posts sent from unrecognized email addresses).
     *
     * @since    1.0.1
     *
     * @return   integer    $id
     */
    protected function get_admin_id() {
        global $wpdb;

        $id = $wpdb->get_var( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key='wp_capabilities' AND meta_value LIKE '%administrator%' ORDER BY user_id LIMIT 1" );
        return $id;
    }

    /**
     * Establishes the connection to the mailserver.
     *
     * @since    1.0.0
     *
     * @param    array    $options    Options array
     *
     * @return   object
     */
    protected function open_mailbox_connection( $connection_options ) {
        $options = get_option( 'bbt_post_by_email_options' );
        if ( 'POP3' == $options['mailserver_protocol'] ) {
            $this->protocol = 'POP3';
            $connection = new Horde_Imap_Client_Socket_Pop3( $connection_options );
        } else {  // IMAP
            $this->protocol = 'IMAP';
            $connection = new Horde_Imap_Client_Socket( $connection_options );
        }
        $connection->_setInit( 'authmethod', 'USER' );

        try {
            $connection->login();
        }
        catch( Horde_Imap_Client_Exception $e ) {
            //$this->save_error_message( esc_html__( 'An error occurred: ', 'BigBangThemesFramework') . $e->getMessage() );
            return false;
        }

        return $connection;
    }

    /**
     * Closes the connection to the mailserver.
     *
     * @since    1.0.4
     */
    protected function close_connection() {
        $this->connection->shutdown();
    }

    /**
     * Retrieve the list of new message IDs from the server.
     *
     * @since    1.0.0
     *
     * @return   array    Array of message UIDs
     */
    protected function get_messages() {
        if ( ! $this->connection )
            return;

        try {
            // POP3 doesn't understand about read/unread messages
            if ( 'POP3' == $this->protocol ) {
                $test = $this->connection->search( 'INBOX' );
            } else {
                $search_query = new Horde_Imap_Client_Search_Query();
                $search_query->flag( Horde_Imap_Client::FLAG_SEEN, false );
                $test = $this->connection->search( 'INBOX', $search_query );
            }
            $uids = $test['match'];
        }
        catch( Horde_Imap_Client_Exception $e ) {
            //$this->save_error_message( esc_html__( 'An error occurred: ', 'BigBangThemesFramework' ) . $e->getMessage() );
            return false;
        }
        return $uids;
    }

    /**
     * Retrieve message headers.
     *
     * @since    1.0.0
     *
     * @param    int    $uid    Message UID
     *
     * @return   object
     */
    protected function get_message_headers( $uid ) {
        $headerquery = new Horde_Imap_Client_Fetch_Query();
        $headerquery->headerText( array() );
        $headerlist = $this->connection->fetch( 'INBOX', $headerquery, array(
                'ids' => $uid,
            )
        );

        $headers = $headerlist->first()->getHeaderText( 0, Horde_Imap_Client_Data_Fetch::HEADER_PARSE );
        return $headers;
    }

    /**
     * Find the email address of the message sender from the headers.
     *
     * @since    1.0.0
     *
     * @param    object    $headers    Message headers
     *
     * @return   string|false
     */
    protected function get_message_author( $headers ) {
        // Set the author using the email address (From or Reply-To, the last used)
        $author = $headers->getValue( 'From' );
        // $replyto = $headers->getValue( 'Reply-To' );  // this is not used and doesn't make sense

        if ( preg_match( '|[a-z0-9_.-]+@[a-z0-9_.-]+(?!.*<)|i', $author, $matches ) )
            $author = $matches[0];
        else
            $author = trim( $author );

        $author = sanitize_email( $author );

        if ( is_email( $author ) ) {
            return $author;
        }

        return false;  // author not found
    }

    /**
     * Get the date of a message from its headers.
     *
     * @since    1.0.0
     *
     * @param    object    $headers    Message headers
     *
     * @return   string
     */
    protected function get_message_date( $headers ) {
        $date = $headers->getValue( 'Date' );
        $dmonths = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );

        // of the form '20 Mar 2002 20:32:37'
        $ddate = trim( $date );
        if ( strpos( $ddate, ',' ) ) {
            $ddate = trim( substr( $ddate, strpos( $ddate, ',' ) + 1, strlen( $ddate ) ) );
        }

        $date_arr = explode( ' ', $ddate );
        $date_time = explode( ':', $date_arr[3] );

        $ddate_H = $date_time[0];
        $ddate_i = $date_time[1];
        $ddate_s = $date_time[2];

        $ddate_m = $date_arr[1];
        $ddate_d = $date_arr[0];
        $ddate_Y = $date_arr[2];

        for ( $j = 0; $j < 12; $j++ ) {
            if ( $ddate_m == $dmonths[$j] ) {
                $ddate_m = $j+1;
            }
        }

        $time_zn = intval( $date_arr[4] ) * 36;
        $ddate_U = gmmktime( $ddate_H, $ddate_i, $ddate_s, $ddate_m, $ddate_d, $ddate_Y );
        $ddate_U = $ddate_U - $time_zn;

        return $ddate_U;
    }

    /**
     * Get the content of a message from the mailserver.
     *
     * @since    1.0.0
     *
     * @param    int       Message UID
     *
     * @return   string    Message content
     */
    protected function get_message_body( $uid ) {
        $query = new Horde_Imap_Client_Fetch_Query();
        $query->structure();

        $list = $this->connection->fetch( 'INBOX', $query, array(
                'ids' => $uid,
            )
        );

        $part = $list->first()->getStructure();
        $body_id = $part->findBody('html');
        if ( is_null( $body_id ) ) {
            $body_id = $part->findBody();
        }
        $body = $part->getPart( $body_id );

        $query2 = new Horde_Imap_Client_Fetch_Query();
        $query2->bodyPart( $body_id, array(
                'decode' => true,
                'peek' => true,
            )
        );

        $list2 = $this->connection->fetch( 'INBOX', $query2, array(
                'ids' => $uid,
            )
        );

        $message2 = $list2->first();
        $content = $message2->getBodyPart( $body_id );
        if ( ! $message2->getBodyPartDecode( $body_id ) ) {
            // Quick way to transfer decode contents
            $body->setContents( $content );
            $content = $body->getContents();
        }

        $content = strip_tags( $content, '<img><p><br><i><b><u><em><strong><strike><font><span><div><style><a>' );
        $content = trim( $content );

        // encode to UTF-8; this fixes up unicode characters like smart quotes, accents, etc.
        $charset = $body->getCharset();
        if ( 'iso-8859-1' == $charset ) {
            $content = utf8_encode( $content );
        } elseif ( function_exists( 'iconv' ) ) {
            $content = iconv( $charset, 'UTF-8', $content );
        }

        return $content;
    }

    /**
     * Get a message's attachments from the mail server and associate them with the post.
     *
     * @since    1.0.3
     *
     * @param    int       Message UID
     * @param    int       ID of the Post to attach to
     *
     * @return   int       Number of attachments saved
     */
    protected function save_attachments( $uid, $postID ) {
        $query = new Horde_Imap_Client_Fetch_Query();
        $query->structure();

        $list = $this->connection->fetch( 'INBOX', $query, array(
                'ids' => $uid,
            )
        );

        $part = $list->first()->getStructure();
        $map = $part->ContentTypeMap();

        $attachment_count = 0;
        $post_thumbnail = false;

        $att_ids = array();
        foreach ( $map as $key => $value ) {
            $p = $part->getPart( $key );

            if ( 'attachment' == $p->getDisposition() ) {
                $mime_id = $key;
                $filename = sanitize_file_name( $p->getName() );
                $filetype = $p->getType();

                $query2 = new Horde_Imap_Client_Fetch_Query();
                $query2->bodyPart( $mime_id, array(
                        'decode' => true,
                        'peek' => true,
                    )
                );

                $list2 = $this->connection->fetch( 'INBOX', $query2, array(
                        'ids' => $uid,
                    )
                );

                $message = $list2->first();

                $image_data = $message->getBodyPart( $mime_id );
                $image_data_decoded = base64_decode( $image_data );

                $upload_dir = wp_upload_dir();
                $directory = $upload_dir['basedir'] . $upload_dir['subdir'];

                wp_mkdir_p( $directory );
                file_put_contents( $directory . '/' . $filename, $image_data_decoded );

                // add attachment to the post
                $attachment_args = array(
                    'post_title' => $filename,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_mime_type' => $filetype,
                );
                include_once( ABSPATH . 'wp-admin/includes/image.php' );

                $attachment_id = wp_insert_attachment( $attachment_args, $directory . '/' . $filename, $postID );
                $attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $directory . '/' . $filename );
                wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
                $attachment_count++;
                $att_ids[] = $attachment_id;

                // make the first image attachment the featured image
                $image_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' );
                if ( false == $post_thumbnail && in_array( $filetype, $image_types ) ) {
                    set_post_thumbnail( $postID, $attachment_id );
                    $post_thumbnail = true;
                }
            }
        }

        return $att_ids;
    }

    /**
     * Mark a list of messages read on the server.
     *
     * @since    1.0.0
     *
     * @param    array    $uids      UIDs of messages that have been processed
     * @param    bool     $delete    Whether to delete read messages
     */
    protected function mark_as_read( $uids, $delete=false ) {
        if ( ! $this->connection )
            return;

        $flag = Horde_Imap_Client::FLAG_SEEN;
        if ( $delete || ( 'POP3' == $this->protocol ) )
            $flag = Horde_Imap_Client::FLAG_DELETED;

        try {
            $this->connection->store( 'INBOX', array(
                    'add' => array( $flag ),
                    'ids' => $uids,
                )
            );
        }
        catch ( Horde_Imap_Client_Exception $e ) {
            //$this->save_error_message( esc_html__( 'An error occurred: ', 'BigBangThemesFramework' ) . $e->getMessage() );
        }
    }

    /**
     * Look for a shortcode and return its arguments.
     *
     * @since    1.0.2
     *
     * @param    string    $shortcode    Shortcode to look for
     *
     * @param    string    $text         Text to search within
     *
     * @return   array     $args         Shortcode arguments
     */
    protected function find_shortcode( $shortcode, $text ) {
        if ( preg_match( "/\[$shortcode\s(.*?)\]/i", $text, $matches ) ) {
            return explode( ' ', $matches[1] );
        }
        return array();
    }

    /**
     * Filter shortcodes out of the message content.
     *
     * @since    1.0.2
     *
     * @param    string    $text         Text to search within
     *
     * @return   string    $text         Filtered text
     */
    protected function filter_valid_shortcodes( $text ) {
        $valid_shortcodes = array( 'tag', 'category', 'pin' );

        // get all registered custom taxonomies
        $args = array(
            'public'   => true,
            '_builtin' => false,
        );
        $registered_taxonomies = get_taxonomies( $args, 'names', 'and' );

        if ( $registered_taxonomies ) {
            foreach ( $registered_taxonomies as $taxonomy ) {
                $valid_shortcodes[] = $taxonomy;
            }
        }

        foreach ( $valid_shortcodes as $shortcode ) {
            $text = preg_replace( "/\[$shortcode\s(.*?)\]/i", '', $text );
        }
        return $text;
    }

    /**
     * Save an error message to the log file.
     *
     * @since    0.9.9
     *
     * @param    string    $message    Error to save to the log.
     */
    /*protected function save_error_message( $message ) {
        $this->save_log_message( $message, true );
    }*/

    /**
     * Save a message to the log file.
     *
     * @since    0.9.9
     *
     * @param    string    $message    Message to save to the log.
     */
    /*protected function save_log_message( $message, $error=false ) {
        $log = get_option( 'post_by_email_log', array() );

        array_unshift( $log, array(
                'timestamp' => current_time( 'timestamp' ),
                'message' => $message,
            )
        );

        update_option( 'post_by_email_log', $log );

        if ( $error ) {
            $options = get_option( 'bbt_post_by_email_options' );
            $options['status'] = 'error';
            update_option( 'bbt_post_by_email_options', $options );

            // clear the transient so the user can trigger another check right away
            delete_transient( 'mailserver_last_checked' );
        }
    }*/

    /**
     * Set the plugin include path and register the autoloader.
     *
     * @since 0.9.7
     */
    public static function load() {
        spl_autoload_register( array( 'BBT_Post_By_Email', 'autoload' ) );
    }

    /**
     * Autoload class libraries as needed.
     *
     * @since 0.9.7
     *
     * @param    string    $class    Class name of requested object.
     */
    public static function autoload( $class ) {
        // We're only interested in autoloading Horde includes.
        if ( 0 !== strpos( $class, 'Horde' ) ) {
            return;
        }

        // Replace all underscores in the class name with slashes.
        // ex: we expect the class Horde_Imap_Client to be defined in Horde/Imap/Client.php.
        $filename = str_replace( array( '_', '\\' ), '/', $class );
        $filename = BBT_PL_DIR . '/post_by_email/' . $filename . '.php';
        if ( file_exists( $filename ) ) {
            require_once( $filename );
        }
    }
}