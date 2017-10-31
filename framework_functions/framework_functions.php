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

/**
 * Remove actions from Customify plugin
 */
/*add_action('wp_head', 'bbt_remove_customify_action', 10);
function bbt_remove_customify_action()
{
    if(class_exists('PixCustomifyPlugin')) {
        remove_action('wp_head', array('Customify_Font_Selector', 'output_font_dynamic_style'), 101);
        remove_action('wp_head', array(PixCustomifyPlugin::instance(), 'output_dynamic_style'), 99);
    }
}*/


function bbt_toco_ggo($key){
    $toco_options = get_option('toco_options');
    $array = $toco_options['general'];
    if (is_array($array))
        foreach($array as $item){
            if (is_array($item) && array_key_exists($key,$item))
                return $item[$key];
        }
    return NULL;
}

//toco constants
{
    if (bbt_toco_ggo('theme_big_name')) {
        if (!defined('THEME_BIG_NAME'))
            define('THEME_BIG_NAME', bbt_toco_ggo('theme_big_name'));
    } else {
        if (!defined('THEME_BIG_NAME'))
            define('THEME_BIG_NAME', 'ToCo Theme');
    }
    if (!defined('THEME_SMALL_NAME'))
        define('THEME_SMALL_NAME', str_replace(' ', '_', strtolower(THEME_BIG_NAME)));
}

add_filter('customify_filter_fields', 'bbt_customify_filter_theme_customizer_options', 999 );
function bbt_customify_filter_theme_customizer_options( $config ) {
    //remove all default added panels
    if(isset($config['sections']['presets_section'])) unset($config['sections']['presets_section']);
    if(isset($config['sections']['colors_section'])) unset($config['sections']['colors_section']);
    if(isset($config['sections']['typography_section'])) unset($config['sections']['typography_section']);
    if(isset($config['sections']['backgrounds_section'])) unset($config['sections']['backgrounds_section']);
    if(isset($config['sections']['layout_options'])) unset($config['sections']['layout_options']);
    // usually the sections key will be here, but a check won't hurt
    if ( ! isset($config['sections']) ) {
        $config['sections'] = array();
    }

    if(file_exists(get_template_directory() . '/theme_config/theme_options'))
    {
        //get files from directory
        $files = glob(get_template_directory() . '/theme_config/theme_options/*.php');
        if(!empty($files))
        {
            //options name to retrieve
            $config['opt-name'] = 'theme_options';

            foreach($files as $file)
            {
                if(file_exists($file))
                {
                    include $file;
                }
            }

            // this means that we add a new entry named "theme_added_settings" in the sections area
            $config['sections'] = $options;
        }

    }
    // when working with filters always return filtered value
    return $config;
}

//get option from DB
if(!function_exists('bbt_get_db_option')){
    function bbt_get_db_option($setting_key){
        $theme_customizer_options = get_theme_mod('theme_options');
        $theme_options = get_option(THEME_SMALL_NAME . '_settings');

        /*echo '<pre>';
        print_r($theme_customizer_options);
        echo '</pre>';*/

        //get option from theme customizer if exists
        if(!empty($theme_customizer_options) && isset($theme_customizer_options[$setting_key]))
        {
            return $theme_customizer_options[$setting_key];
        }
        //get option from toco if exists
        elseif (isset($theme_options[$setting_key]))
        {
            if (toco_go($setting_key . "_zoom")) {    //if is map option
                return toco_gmap($setting_key, $setting_key, $setting_key, 'true', true, $theme_options[$setting_key]);
            }
            return $theme_options[$setting_key];
        }
        else
        {
            return NULL;
        }
    }
}