<?php
class BBT_Category_Builder{
    
    function __construct(){
        if(!(defined('BBT_CATEGORY_BUILDER_ENABLE') && BBT_CATEGORY_BUILDER_ENABLE)) return;

        //add category builder post type
        add_action('init', array( $this, 'bbt_create_category_builder_post_type'));
        add_action('init', array( $this, 'bbt_add_vc_to_builder'), 1);
        //add categories meta box
        add_action( 'add_meta_boxes', array( $this, 'bbt_register_meta_boxes'), 99 , 2);
        //save categories metabox values
        add_action( 'save_post_' . $this->bbt_get_post_type(), array( $this, 'bbt_save_slide_values') );
        //add custom columns in edit.php?post_type=bbt_category_builder
        add_filter('manage_' . $this->bbt_get_post_type() . '_posts_columns', array($this, 'bbt_add_post_custom_columns'), 10, 1);
        //menage custom columns in edit.php?post_type=bbt_category_builder
        add_filter('manage_' . $this->bbt_get_post_type() . '_posts_custom_column', array($this, 'bbt_menage_post_custom_columns'), 10, 2);
    }

    /**
     * bbt_get_post_type
     * @access public
     */
    public function bbt_get_post_type()
    {
        return 'bbt_category_builder';
    }

    /**
     * bbt_create_category_builder_post_type
     * create bbt_category_builder post type
     * @access public
     */
    public function bbt_create_category_builder_post_type()
    {
        // Category Builder
        $labels = array(
            'name' => __('Category Builder', 'bbt_fw_plugin'),
            'singular_name' => __('Layouts','bbt_fw_plugin'),
            'add_new' => __('Add New', 'bbt_fw_plugin'),
            'add_new_item' => __('Add New Layout', 'bbt_fw_plugin'),
            'edit_item' => __('Edit Layout', 'bbt_fw_plugin'),
            'new_item' => __('New Layout', 'bbt_fw_plugin'),
            'all_items' => __('All Layouts', 'bbt_fw_plugin'),
            'view_item' => __('View Layout', 'bbt_fw_plugin'),
            'search_items' => __('Search Layouts', 'bbt_fw_plugin'),
            'not_found' =>  __('Nothing found', 'bbt_fw_plugin'),
            'not_found_in_trash' => __('Nothing found in Trash', 'bbt_fw_plugin'),
            'parent_item_colon' => ''
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'query_var' => true,
            //'menu_icon' => get_template_directory_uri() . '/images/icons/testimonials.png',
            'rewrite' => true,
            'menu_position' => 60,
            'supports' => array('title','editor')
        );

        register_post_type( $this->bbt_get_post_type()  , $args );
    }

    /**
     * bbt_add_post_custom_columns
     * add custom columns in edit.php?post_type=bbt_category_builder
     * @access public
     */
    public function bbt_add_post_custom_columns($columns)
    {
        return array(
            'cb' => $columns['cb'],
            'title' => $columns['title'],
            'categ' => esc_html__('Categories', 'bbt_fw_plugin'),
            'date' => $columns['date'],
        );
    }

    /**
     * bbt_menage_post_custom_columns
     * menage custom colums in edit.php?post_type=bbt_category_builder
     * @access public
     */
    public function bbt_menage_post_custom_columns($column, $post_id)
    {
        switch ($column) {
            case 'categ' :
                $terms = wp_get_post_terms($post_id, 'category');

                if(!empty($terms))
                {
                    $c = 1;
                    foreach ($terms as $term)
                    {
                        echo ($c == count($terms))
                            ?
                            '<a href="edit.php?post_type=bbt_category_builder&category_name='.esc_attr($term->slug).'">'.esc_html($term->name).'</a>'
                            :
                            '<a href="edit.php?post_type=bbt_category_builder&category_name='.esc_attr($term->slug).'">'.esc_html($term->name).'</a>, ';
                        $c++;
                    }
                }
                else
                    echo esc_html__('No Categories', 'bbt_fw_plugin');

                break;
            default :
                break;
        }
    }

    /**
     * bbt_register_meta_boxes
     * add the category metabox in post type
     * @access public
     */
    function bbt_register_meta_boxes($post_type, $post) {

        if($post->post_type === $this->bbt_get_post_type()) {
            # Add configurations metabox
            add_meta_box(
                'linkcategorydiv',
                esc_html__('Categories', 'bbt_fw_plugin'),
                array($this, 'bbt_categories_meta_box'),
                'bbt_category_builder',
                'side',
                'default'
            );
        }
    }

    /**
     * bbt_link_categories_meta_box
     * metabox view template
     * @access public
     */
    public function bbt_categories_meta_box($post) {
        post_categories_meta_box( $post, array() );
    }

    /**
     * bbt_save_slide_values
     * save clicked slide options values
     * @access public
     */
    public function bbt_save_slide_values($post_id)
    {
        if (!$this->bbt_is_real_post_save($post_id))
            return;

        if(!isset($_POST['post_category']))
            return;

        wp_set_post_categories( $post_id, $_POST['post_category']);

        $saved_values = get_option('bbt_category_builder');
        $saved_values = !empty($saved_values) ? array($post_id => $_POST['post_category']) + $saved_values : array($post_id => $_POST['post_category']);

        update_option('bbt_category_builder', $saved_values);

        //die();
    }

    /**
     * Retrieve a list of the most popular terms from the specified taxonomy.
     *
     * If the $echo argument is true then the elements for a list of checkbox
     * `<input>` elements labelled with the names of the selected terms is output.
     * If the $post_ID global isn't empty then the terms associated with that
     * post will be marked as checked.
     *
     * @since 2.5.0
     *
     * @param string $taxonomy Taxonomy to retrieve terms from.
     * @param int $default Not used.
     * @param int $number Number of terms to retrieve. Defaults to 10.
     * @param bool $echo Optionally output the list as well. Defaults to true.
     * @param array $saved_values Array of saved values
     * @return array List of popular term IDs.
     */
    function bbt_popular_terms_checklist( $taxonomy, $default = 0, $number = 10, $echo = true, $saved_values = array() ) {
        $post = get_post();

        if ( $post && $post->ID && !empty($saved_values) )
            $checked_terms = $saved_values;
        else
            $checked_terms = array();

        $terms = get_terms( $taxonomy, array( 'orderby' => 'count', 'order' => 'DESC', 'number' => $number, 'hierarchical' => false ) );

        $tax = get_taxonomy($taxonomy);

        $popular_ids = array();
        foreach ( (array) $terms as $term ) {
            $popular_ids[] = $term->term_id;
            if ( !$echo ) // Hack for Ajax use.
                continue;
            $id = "popular-$taxonomy-$term->term_id";
            $checked = in_array( $term->term_id, $checked_terms ) ? 'checked="checked"' : '';
            ?>

            <li id="<?php echo $id; ?>" class="popular-category">
                <label class="selectit">
                    <input id="in-<?php echo $id; ?>" type="checkbox" <?php echo $checked; ?> value="<?php echo (int) $term->term_id; ?>" <?php disabled( ! current_user_can( $tax->cap->assign_terms ) ); ?> />
                    <?php
                    /** This filter is documented in wp-includes/category-template.php */
                    echo esc_html( apply_filters( 'the_category', $term->name ) );
                    ?>
                </label>
            </li>

            <?php
        }
        return $popular_ids;
    }

    /**
 * This function is used in 'post_updated' action
 *
 * @param $post_id
 * @return bool
 */
    function bbt_is_real_post_save($post_id)
    {
        return !(
            wp_is_post_revision($post_id)
            || wp_is_post_autosave($post_id)
            || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || (defined('DOING_AJAX') && DOING_AJAX)
        );
    }

    /**
     * Add visual builder to builder posts
     *
     */
    function bbt_add_vc_to_builder()
    {
        if(is_plugin_active('js_composer/js_composer.php')){
            if(function_exists('vc_set_default_editor_post_types'))
            {
                $list = array(
                    'bbt_category_builder',
                    'page'
                );
                vc_set_default_editor_post_types( $list );
            }
        }
    }
}