<?php
class BBT_Plugin_Installer{
    //static public $bbt_api_url = 'http://localhost/bigbang/showoff/user/';
    //private $api_url = "http://localhost/bigbang/showoff/api-listener/";
    static public $bbt_api_url = 'http://bigbangthemes.net/user/';
    private $api_url = "http://bigbangthemes.net/api-listener/";

    function __construct(){
        if(defined('BBT_THEME_PRODUCT_KEY') && BBT_THEME_PRODUCT_KEY) {
            add_action('register_sidebar', array($this, 'bbt_theme_admin_init'));
            add_action('admin_menu', array($this, 'bbt_theme_admin_menu'));
            add_action('admin_menu', array($this, 'bbt_theme_admin_product_key_submenu'));
            add_action('admin_menu', array($this, 'bbt_edit_admin_menus'));
            add_action('admin_init', array( $this, 'bbt_theme_update'));
            add_action('admin_enqueue_scripts', array($this, 'bbt_theme_admin_pages'));
            add_action('admin_notices', array($this, 'bbt_admin_notices'), 99);
            add_action('admin_notices', array($this, 'bbt_update_notice'));
        }
    }

    // =============================================================================
    // Menus
    // =============================================================================
    function bbt_theme_admin_menu() {

        if (!function_exists('current_user_can')):
            require_once(ABSPATH. "wp-includes/pluggable.php");
        endif;

        if (!current_user_can('administrator')):
            return;
        endif;

        $bbt_menu_welcome = add_menu_page(
            bbt_parent_theme_name(),
            bbt_parent_theme_name(),
            //esc_html__( 'Tools', 'BigBangThemesFramework' ),
            'administrator',
            'bbt_welcome_theme',
            array( $this, 'bbt_theme_tools_page' ),
            '',
            3
        );
    }

    function bbt_theme_admin_product_key_submenu() {

        if (!function_exists('current_user_can')):
            require_once(ABSPATH. "wp-includes/pluggable.php");
        endif;

        if (!current_user_can('administrator')):
            return;
        endif;

        $bbt_menu_product_key = add_submenu_page(
            'bbt_welcome_theme',
            esc_html__( 'Product Key', 'BigBangThemesFramework' ),
            esc_html__( 'Product Key', 'BigBangThemesFramework' ),
            'administrator',
            'bbt_product_key_page',
            array( $this, 'bbt_theme_key_page' )
        );
    }

    function bbt_theme_key_page()
    {
        require_once BBT_PL_DIR . 'plugins-installer/pages/registration.php';
    }

    function bbt_theme_tools_page()
    {
        require_once BBT_PL_DIR . 'plugins-installer/pages/tools.php';
    }

    function bbt_edit_admin_menus() {
        global $submenu;

        if (!function_exists('current_user_can')):
            require_once(ABSPATH. "wp-includes/pluggable.php");
        endif;

        if (!current_user_can('administrator')):
            return;
        endif;
        $submenu['bbt_welcome_theme'][0][0] = esc_html__( 'Tools', 'BigBangThemesFramework' );
    }
    // =============================================================================
    // END Menus
    // =============================================================================

    // =============================================================================
    // Plug-ins
    // =============================================================================

    function bbt_theme_plugin_links( $item )
    {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $installed_plugins = get_plugins();

        $item['sanitized_plugin'] = $item['name'];

        // We have a repo plugin
        if ( ! $item['version'] ) {
            $item['version'] = TGM_Plugin_Activation::$instance->does_plugin_have_update( $item['slug'] );
        }

        /** We need to display the 'Install' hover link */
        if ( ! isset( $installed_plugins[$item['file_path']] ) ) {
            $actions = array(
                'install' => sprintf(
                    '<a href="%1$s" class="button" title="'.esc_html__('Install','BigBangThemesFramework').' %2$s">'.esc_html__('Install Now','BigBangThemesFramework').'</a>',
                    esc_url( wp_nonce_url(
                        add_query_arg(
                            array(
                                'page'          => urlencode( TGM_Plugin_Activation::$instance->menu ),
                                'plugin'        => urlencode( $item['slug'] ),
                                'plugin_name'   => urlencode( $item['sanitized_plugin'] ),
                                'plugin_source' => urlencode( $item['source'] ),
                                'tgmpa-install' => 'install-plugin',
                                'return_url'    => network_admin_url( 'admin.php?page=bbt_welcome_theme' )
                            ),
                            TGM_Plugin_Activation::$instance->get_tgmpa_url()
                        ),
                        'tgmpa-install',
                        'tgmpa-nonce'
                    ) ),
                    $item['sanitized_plugin']
                ),
            );
        }
        elseif (version_compare( $installed_plugins[$item['file_path']]['Version'], $item['version'], '<' ) && is_plugin_active( $item['file_path'] )){
            $actions = array(
                'update' => sprintf(
                    '<a href="%1$s" class="button button-update" title="'.esc_html__('Update','BigBangThemesFramework').' %2$s"><span class="dashicons dashicons-update"></span> '.esc_html__('Update','BigBangThemesFramework').'</a>',
                    wp_nonce_url(
                        add_query_arg(
                            array(
                                'page'          => urlencode( TGM_Plugin_Activation::$instance->menu ),
                                'plugin'        => urlencode( $item['slug'] ),

                                'tgmpa-update'  => 'update-plugin',
                                'plugin_source' => urlencode( $item['source'] ),
                                'version'       => urlencode( $item['version'] ),
                                'return_url'    => network_admin_url( 'admin.php?page=bbt_welcome_theme' )
                            ),
                            TGM_Plugin_Activation::$instance->get_tgmpa_url()
                        ),
                        'tgmpa-update',
                        'tgmpa-nonce'
                    ),
                    $item['sanitized_plugin']
                ),
                'deactivate' => sprintf(
                    '<a href="%1$s" class="button bbt-deactivate-update" title="'.esc_html__('Deactivate','BigBangThemesFramework').' %2$s">'.esc_html__('Deactivate','BigBangThemesFramework').'</a>',
                    esc_url( add_query_arg(
                        array(
                            'plugin'                 => urlencode( $item['slug'] ),
                            'plugin_name'            => urlencode( $item['sanitized_plugin'] ),
                            'plugin_source'          => urlencode( $item['source'] ),
                            'bbt-deactivate'       => 'deactivate-plugin',
                            'bbt-deactivate-nonce' => wp_create_nonce( 'bbt-deactivate' ),
                        ),
                        admin_url( 'admin.php?page=bbt_welcome_theme' )
                    ) ),
                    $item['sanitized_plugin']
                ),
            );
        }
        /** We need to display the 'Activate' hover link */
        elseif ( is_plugin_inactive( $item['file_path'] ) ) {
            $actions = array(
                'activate' => sprintf(
                    '<a href="%1$s" class="button button-primary" title="'.esc_html__('Activate','BigBangThemesFramework').' %2$s">'.esc_html__('Activate','BigBangThemesFramework').'</a>',
                    esc_url( add_query_arg(
                        array(
                            'plugin'               => urlencode( $item['slug'] ),
                            'plugin_name'          => urlencode( $item['sanitized_plugin'] ),
                            'plugin_source'        => urlencode( $item['source'] ),
                            'bbt-activate'       => 'activate-plugin',
                            'bbt-activate-nonce' => wp_create_nonce( 'bbt-activate' ),
                        ),
                        admin_url( 'admin.php?page=bbt_welcome_theme' )
                    ) ),
                    $item['sanitized_plugin']
                ),
            );
        }
        /** We need to display the 'Update' hover link */
        elseif ( version_compare( $installed_plugins[$item['file_path']]['Version'], $item['version'], '<' ) ) {
            $actions = array(
                'update' => sprintf(
                    '<a href="%1$s" class="button button-update" title="'.esc_html__('Update','BigBangThemesFramework').' %2$s"><span class="dashicons dashicons-update"></span> '.esc_html__('Update','BigBangThemesFramework').'</a>',
                    wp_nonce_url(
                        add_query_arg(
                            array(
                                'page'          => urlencode( TGM_Plugin_Activation::$instance->menu ),
                                'plugin'        => urlencode( $item['slug'] ),

                                'tgmpa-update'  => 'update-plugin',
                                'plugin_source' => urlencode( $item['source'] ),
                                'version'       => urlencode( $item['version'] ),
                                'return_url'    => network_admin_url( 'admin.php?page=bbt_welcome_theme' )
                            ),
                            TGM_Plugin_Activation::$instance->get_tgmpa_url()
                        ),
                        'tgmpa-update',
                        'tgmpa-nonce'
                    ),
                    $item['sanitized_plugin']
                ),
            );
        }
        elseif ( is_plugin_active( $item['file_path'] ) ) {
            $actions = array(
                'deactivate' => sprintf(
                    '<a href="%1$s" class="button" title="'.esc_html__('Deactivate','BigBangThemesFramework').' %2$s">'.esc_html__('Deactivate','BigBangThemesFramework').'</a>',
                    esc_url( add_query_arg(
                        array(
                            'plugin'                 => urlencode( $item['slug'] ),
                            'plugin_name'            => urlencode( $item['sanitized_plugin'] ),
                            'plugin_source'          => urlencode( $item['source'] ),
                            'bbt-deactivate'       => 'deactivate-plugin',
                            'bbt-deactivate-nonce' => wp_create_nonce( 'bbt-deactivate' ),
                        ),
                        admin_url( 'admin.php?page=bbt_welcome_theme' )
                    ) ),
                    $item['sanitized_plugin']
                ),
            );
        }

        return $actions;
    }

    // =============================================================================
    // Admin Init
    // =============================================================================

    function bbt_theme_admin_init() {

        if ( isset( $_GET['bbt-deactivate'] ) && $_GET['bbt-deactivate'] == 'deactivate-plugin' ) {

            check_admin_referer( 'bbt-deactivate', 'bbt-deactivate-nonce' );

            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $plugins = get_plugins();

            foreach ( $plugins as $plugin_name => $plugin ) {
                $plugin_set_name = strtolower($plugin['Name']);
                $plugin_get_name = strtolower($_GET['plugin_name']);

                if ( $plugin_set_name == $plugin_get_name ) {
                    deactivate_plugins( $plugin_name );
                }
            }

            if ( wp_redirect( admin_url( 'admin.php?page=bbt_welcome_theme' ) ) ) {
                exit;
            }
        }

        if ( isset( $_GET['bbt-activate'] ) && $_GET['bbt-activate'] == 'activate-plugin' ) {

            check_admin_referer( 'bbt-activate', 'bbt-activate-nonce' );

            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $plugins = get_plugins();

            foreach ( $plugins as $plugin_name => $plugin ) {
                $plugin_set_name = strtolower($plugin['Name']);
                $plugin_get_name = strtolower($_GET['plugin_name']);

                if ( $plugin_set_name == $plugin_get_name ) {
                    activate_plugin( $plugin_name );
                }
            }

            if ( wp_redirect( admin_url( 'admin.php?page=bbt_welcome_theme' ) ) ) {
                exit;
            }
        }
    }

    // =============================================================================
    // Theme Updater
    // =============================================================================

    function bbt_theme_update()
    {
        global $wp_filesystem;

        if (get_option("bbt_".THEME_FOLDER_NAME."_license") && get_option("bbt_". THEME_FOLDER_NAME ."_valid_key"))
        {
            $license_key = get_option("bbt_".THEME_FOLDER_NAME."_license");
        }
        else
        {
            $license_key = '';
        }

        if(!empty($license_key)) {
            require_once(BBT_PL_DIR . '/plugins-installer/bbt_class-updater.php');

            $theme_update = new BBT_Updater($license_key, self::bbt_theme_version());
        }
    }

    function bbt_admin_notices() {
        $remote_ver = get_option("bbt_".THEME_FOLDER_NAME."_remote_ver") ? get_option("bbt_".THEME_FOLDER_NAME."_remote_ver") : self::bbt_theme_version();
        $local_ver = self::bbt_theme_version();

        if(!version_compare($local_ver, $remote_ver, '<'))
        {
            $valid_key = get_option("bbt_". THEME_FOLDER_NAME ."_valid_key");
            if ( !$valid_key || empty($valid_key)){

                if ( ! isset($_COOKIE["notice_product_key"]) || $_COOKIE["notice_product_key"] != "1" ) {
                    $message = sprintf(
                        esc_html__('Enter your product key to start receiving automatic updates and support. Go to %1$sProduct Activation%2$s', 'BigBangThemesFramework'),
                        '<a href="' . admin_url('admin.php?page=bbt_product_key_page') . '">', '</a>'
                    );

                    echo '<div class="notice is-dismissible error bbt_admin_notices notice_product_key">
                    <p><b>' . bbt_parent_theme_name() . '</b> - ' . $message . '.</p>
                    </div>';
                }
            }
        }
    }

    function bbt_validate_license($license_key)
    {
        if (empty($license_key))
        {
            return FALSE;
        }
        else
        {
            $api_url = $this->api_url;
            $theme = wp_get_theme();
            $args = array(
                'method' => 'POST',
                'timeout' => 100,
                'body' => array( 'license_key' => $license_key,  'site_url' => get_site_url(), 'theme_name' => bbt_parent_theme_name() )
            );

            $response = wp_remote_post( $api_url, $args );

            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                $request_msg = 'Something went wrong:'.$error_message.'. Please try again!';
            } else {
                $rsp = json_decode($response['body']);

                if(!isset($rsp->result)) return;

                switch ($rsp->result)
                {
                    case '0':
                        $response['text'] = esc_html__('Something went wrong. Please try again!','BigBangThemesFramework');
                        break;

                    case '1':
                        update_option("bbt_".THEME_FOLDER_NAME."_license", $license_key);
                        update_option("bbt_". THEME_FOLDER_NAME ."_valid_key", true);
                        break;

                    case '2':
                        $response['text'] = esc_html__('The product key you entered is not valid.','BigBangThemesFramework');
                        break;

                    case '3':
                        $response['domain'] = $rsp->correct_domain;
                        $response['text'] = esc_html__('The product key you entered is not valid.','BigBangThemesFramework');
                        break;
                    case '4':
                        $response['text'] = esc_html__('Theme name doesn\'t match the product key item.','BigBangThemesFramework');
                        break;
                }
            }

            return $response;
        }
    }

    function bbt_update_notice()
    {
        $remote_ver = get_option("bbt_".THEME_FOLDER_NAME."_remote_ver") ? get_option("bbt_".THEME_FOLDER_NAME."_remote_ver") : self::bbt_theme_version();
        $local_ver = self::bbt_theme_version();

        if(version_compare($local_ver, $remote_ver, '<'))
        {
            if( function_exists('wp_get_theme') ) {
                $theme_name = '<strong>'. wp_get_theme(get_template()) .'</strong>';
            }

            $remove_key = ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['action']) && $_POST['action'] == 'bbt-delkey')) ? true : false;
            $valid_key = get_option("bbt_". THEME_FOLDER_NAME ."_valid_key");

            if ( (!$valid_key || empty($valid_key)) || $remove_key ) {
                $message1 = sprintf(
                    esc_html__('There is an update available for the %1$s theme. Go to %2$sProduct Activation%3$s to enable theme updates','BigBangThemesFramework'),
                    $theme_name, '<a href="' . admin_url( 'admin.php?page=bbt_product_key_page' ) . '">', '</a>'
                );

                echo '<div class="notice is-dismissible error bbt_update_notices notice_product_key">
					<p>'.$message1.'.</p>
					</div>';

            }

            if ( $valid_key && !empty($valid_key) && !$remove_key ) {
                $message2 = sprintf(
                    esc_html__('There is an update available for the %1$s theme. %2$sUpdate now%3$s','BigBangThemesFramework'),
                    $theme_name, '<a href="' . admin_url() . 'update-core.php">', '</a>'
                );

                echo '<div class="notice is-dismissible error bbt_update_notices bbt_update_notices_yes notice_product_key">
				<p>'.$message2.'.</p>
				</div>';

            }
        }
    }

    // =============================================================================
    // Styles / Scripts
    // =============================================================================

    function bbt_theme_admin_pages() {
        // Get Plugin Path
        $plugin_path = plugin_dir_url( __FILE__ );

        wp_enqueue_style(	"bbt_plugin-admin_css", $plugin_path . "css/plugin-admin.css", false, 1.1, "all" );
        wp_enqueue_script(	"bbt_plugin-admin_js", 	$plugin_path . "js/plugin-admin.js", 	array(), false, null );
    }

    static function bbt_theme_version() {
        $bbt_theme = wp_get_theme();
        if($bbt_theme->parent()):
            return $bbt_theme->parent()->get('Version');
        else:
            return $bbt_theme->get('Version');
        endif;
    }
}