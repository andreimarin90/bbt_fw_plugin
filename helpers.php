<?php
/**
 * Enqueue admin plugin scripts
 *
 */
add_action('admin_enqueue_scripts', 'bbt_fw_plugin_enqueue_admin_scripts');
function bbt_fw_plugin_enqueue_admin_scripts() {
    wp_enqueue_style(	"bbt_admin_style", BBT_PL_URL . "/src/css/bbt_admin_style.css", false, 1.0, "all" );
    //wp_enqueue_script(	"bbt_plugin-admin_js", 	$plugin_path . "js/plugin-admin.js", 	array(), false, null );
}

/**
 * Enqueue frontend plugin scripts
 *
 */
add_action('wp_enqueue_scripts', 'bbt_fw_plugin_enqueue_scripts');
function bbt_fw_plugin_enqueue_scripts() {
    //wp_enqueue_style(	"bbt_frontend_style", BBT_PL_URL . "/src/css/bbt_admin_style.css", false, 1.0, "all" );
    wp_enqueue_script(	"bbt_plugin_framework_js", 	BBT_PL_URL . "/src/js/bbt-plugin-framework.js", array(), false, null );
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

add_action('wp_ajax_bbt_fb_login', 'bbt_fb_login');
add_action('wp_ajax_nopriv_bbt_fb_login', 'bbt_fb_login');
function bbt_fb_login(){
    $user_id = get_current_user_id();
    if($user_id)
        wp_send_json( array('result'=>'alreadyLoggedIn','message'=>'User already logged in','user_id' => $user_id) );

    $fbuser = $_POST['fbuser'];
    $existing_user = get_user_by('email', $fbuser['email']);

    if($existing_user)
    {
        wp_clear_auth_cookie();
        wp_set_current_user( $existing_user->ID );
        wp_set_auth_cookie( $existing_user->ID , true );
        wp_send_json( array('result'=>'existingLoggedIn','message'=>'Logged in Existing User : ' . $existing_user->ID, 'user_id' => $existing_user->ID) );
    }
    $user_data['user_login']    = 'fb-' .$fbuser['id'];
    $user_data['user_pass']     = wp_generate_password();
    $user_data['first_name']    = $fbuser['first_name'];
    $user_data['last_name']     = $fbuser['last_name'];
    $user_data['user_email']    = $fbuser["email"];
    $new_user_id = wp_insert_user( $user_data );

    if( is_wp_error( $new_user_id ) )
    {
        wp_send_json( array('result'=>'errorCreatingUser','message'=>'Error Creating User : ' . $new_user_id->get_error_message()) );
    }
    else
    {
        wp_clear_auth_cookie();
        wp_set_current_user( $new_user_id );
        wp_set_auth_cookie( $new_user_id , true );
        wp_send_json( array('result'=>'newLoggedIn','message'=>'New user created and logged in', 'user_id' => $new_user_id) );
    }
}

function bbt_pl_twitter_login($tw_user){
    $user_id = get_current_user_id();
    if($user_id){
        return json_encode( array('result'=>'alreadyLoggedIn','message'=>'User already logged in', 'user_id' => $user_id) );
    }

    $existing_user = get_user_by('login', 'tw-' . $tw_user->id);
    //echo json_encode( $existing_user );
    if($existing_user){
        wp_clear_auth_cookie();
        wp_set_current_user( $existing_user->ID );
        wp_set_auth_cookie( $existing_user->ID , true );
        return json_encode( array('result'=>'existingLoggedIn','message'=>'Logged in Existing User : ' . $existing_user->ID, 'user_id' => $existing_user->ID) );
    }

    $user_data['user_login']    = 'tw-' . $tw_user->id;
    $user_data['user_pass']     = wp_generate_password();
    $user_data['display_name']  = $tw_user->name;
    $user_data['user_email']    = $tw_user->id . '@twitter.com';	//required so we fake it
    $new_user_id = wp_insert_user( $user_data );
    if( is_wp_error( $new_user_id ) ) {
        return json_encode( array('result'=>'errorCreatingUser','message'=>'Error Creating User : ' . $new_user_id->get_error_message()) );
    }else{
        wp_clear_auth_cookie();
        wp_set_current_user( $new_user_id );
        wp_set_auth_cookie( $new_user_id , true );
        return json_encode( array('result'=>'newLoggedIn','message'=>'New user created and logged in', 'user_id' => $new_user_id) );
    }
}

function bbt_pl_innclude_tw_auth_file(){
    if(isset($_GET['bbt-tw-login'])){
        require BBT_PL_DIR . 'twitteroauth/tw_auth.php';
    }
}
add_action('init', 'bbt_pl_innclude_tw_auth_file');
