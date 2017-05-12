<?php
/**
 * Enqueue admin plugin scripts
 *
 */
add_action('admin_enqueue_scripts', 'bbt_fw_plugin_enqueue_admin_scripts');
function bbt_fw_plugin_enqueue_admin_scripts() {
    wp_enqueue_script( "bbt_plugin-framework-admin_js", BBT_PL_URL . "/src/js/bbt_plugin-framework-admin.js", array('jquery'), false, true );
    wp_enqueue_style( "bbt_frontend_style", BBT_PL_URL . "/src/css/bbt_admin_style.css", false, 1.0, "all" );
}

/**
 * Enqueue frontend plugin scripts
 *
 */
add_action('wp_enqueue_scripts', 'bbt_fw_plugin_enqueue_scripts');
function bbt_fw_plugin_enqueue_scripts() {
    //wp_enqueue_style(	"bbt_frontend_style", BBT_PL_URL . "/src/css/bbt_admin_style.css", false, 1.0, "all" );
    wp_enqueue_script(	"bbt_plugin_framework_js", 	BBT_PL_URL . "/src/js/bbt-plugin-framework.js", array('jquery'), false, true );
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