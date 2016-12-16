<?php
/***********************************************************************************************/
/*  Visual Composer css code decode
/***********************************************************************************************/

if(!function_exists('bbt_vc_style_decode')){
    function bbt_vc_style_decode($css){
        return htmlentities( rawurldecode( base64_decode( $css ) ), ENT_COMPAT, 'UTF-8' );
    }
}

/**
 * bbt_get_builder_posts
 * get builder saved posts
 * @return $builder_posts
 */
function bbt_get_builder_posts()
{
    //get current category id
    $cat_id = get_query_var('cat');
    //get builder saved posts
    $builder_posts = get_option('bbt_category_builder');
    
    return $builder_posts;
}

/**
 * bbt_category_builder_ids
 * get builder saved categories ids
 * @return $builder_categories
 */
function bbt_category_builder_ids($builder_posts)
{
    $builder_categories = array();

    if(!empty($builder_posts))
    {
        foreach($builder_posts as $post_id => $builder_post)
        {
            foreach($builder_post as $val)
            {
                array_push($builder_categories, $val);
            }
        }
    }

    return array_unique($builder_categories);
}

if ( ! function_exists( 'bbt_parent_theme_name' ) ) :
    function bbt_parent_theme_name()
    {
        $theme = wp_get_theme();
        if ($theme->parent()):
            $theme_name = $theme->parent()->get('Name');
        else:
            $theme_name = $theme->get('Name');
        endif;

        return $theme_name;
    }
endif;


/**
 * Includes a view file from plugins extensions root/views/
 * @param  string  $_name    name of the view file
 * @param  string  $_name    name of the view file
 * @param  array  $_data    array of the variables to be sent to the view
 * @param  boolean $__return if false will echo the view else will return it (f or shortcodes use TRUE !!! )
 * @return html            If $__return is set to true , returns the view content
 */
function bbt_plugin_view( $_name, $extension = NULL ,$_data = NULL, $__return = FALSE) {
    $_name = strtolower( $_name );
    if ( !file_exists( BBT_PL_DIR . '/'.$extension.'/views/'.$_name.'.php' ) )
        exit( 'View not found: ' . $_name );
    if ( $_data !== NULL && count( $_data ) > 0 )
        foreach ( $_data as $_name_var => $_value )
            ${$_name_var} = $_value;
    ob_start();

    if($extension == NULL)
        require (BBT_PL_DIR . '/views/'.$_name.'.php') ;
    else
        require (BBT_PL_DIR . '/'.$extension.'/views/'.$_name.'.php') ;

    $buffer = ob_get_clean();
    if ( $__return === TRUE )
        return $buffer;
    else
        print $buffer;
}

if ( ! function_exists( 'bbt_get_view' ) ) :
function bbt_get_view( $_name, $folder = '' ,$_data = NULL, $__return = FALSE) {
    $_name = strtolower( $_name );
    if ( !file_exists( get_stylesheet_directory() . '/'.$folder.'/'.$_name.'.php' ) )
        exit( 'View not found: ' . $_name );
    if ( $_data !== NULL && count( $_data ) > 0 )
        foreach ( $_data as $_name_var => $_value )
            ${$_name_var} = $_value;
    ob_start();

    require (get_stylesheet_directory() . '/'.$folder.'/'.$_name.'.php') ;

    $buffer = ob_get_clean();
    if ( $__return === TRUE )
        return $buffer;
    else
        print $buffer;
}
endif;

if( ! function_exists('bbt_insert_php_code') )
{
    function bbt_insert_php_code($content)
    {
        $will_bontrager_content = $content;
        preg_match_all('!\[bbt_insert_php[^\]]*\](.*?)\[/bbt_insert_php[^\]]*\]!is',$will_bontrager_content,$will_bontrager_matches);
        $will_bontrager_nummatches = count($will_bontrager_matches[0]);
        for( $will_bontrager_i=0; $will_bontrager_i<$will_bontrager_nummatches; $will_bontrager_i++ )
        {
            ob_start();
            eval($will_bontrager_matches[1][$will_bontrager_i]);
            $will_bontrager_replacement = ob_get_contents();
            ob_clean();
            ob_end_flush();
            $will_bontrager_content = preg_replace('/'.preg_quote($will_bontrager_matches[0][$will_bontrager_i],'/').'/',$will_bontrager_replacement,$will_bontrager_content,1);
        }
        return $will_bontrager_content;
    } # function will_bontrager_insert_php()
    add_filter( 'the_content', 'bbt_insert_php_code', 9 );
}