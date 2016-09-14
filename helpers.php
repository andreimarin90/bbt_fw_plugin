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