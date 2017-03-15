<?php
/**
 * Enqueue plugin scripts
 *
 */
add_action('admin_enqueue_scripts', 'bbt_fw_plugin_enqueue_scripts');
function bbt_fw_plugin_enqueue_scripts() {
    wp_enqueue_style(	"bbt_admin_style", BBT_PL_URL . "/src/css/bbt_admin_style.css", false, 1.0, "all" );
    //wp_enqueue_script(	"bbt_plugin-admin_js", 	$plugin_path . "js/plugin-admin.js", 	array(), false, null );
}


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

function bbt_check_external_file($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $retCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $retCode;
}

function verifyPurchase($userName, $apiKey , $purchaseCode, $itemId = false) {
    // Open cURL channel
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/$userName/$apiKey/verify-purchase:$purchaseCode.json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'ENVATO-PURCHASE-VERIFY'); //api requires any user agent to be set

    // Decode returned JSON
    $result = json_decode( curl_exec($ch) , true );

    //check if purchase code is correct
    if ( !empty($result['verify-purchase']['item_id']) && $result['verify-purchase']['item_id'] && isset($result['verify-purchase']['buyer']) ) {
        //if no item name is given - any valid purchase code will work
        if ( !$itemId ) return true;
        //else - also check if purchased item is given item to check
        return $result['verify-purchase']['item_id'] == $itemId;
    }

    //invalid purchase code
    return false;

}

if(isset($_GET['bbt-tw-login'])){
    require BBT_PL_DIR . 'twitteroauth/tw_auth.php';
}