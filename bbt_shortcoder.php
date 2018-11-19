<?php
/**
 * Shortcoder - creates/registers/controls and views shortcodes
 */
class BBT_Shortcoder{

    private static $load;
    private static $config;
    private static $config_location;
    private static $shortcodes_only;

    function __construct(){}

    public static function init(){
        $config_file = locate_template('theme_config/shortcodes-options.php');
        if($config_file !== ''){
            self::$config_location = $config_file;

        }

        if(!class_exists('BBT_Load')) return;

        self::$load = new BBT_Load;

        if(function_exists('vc_map')){
            add_action('vc_before_init',array( 'BBT_Shortcoder' , 'auto_load'),10);
            add_action('vc_before_init', array( 'BBT_Shortcoder' , 'vc_insert_sc') , 11);
        }else{
            add_action('init',array( 'BBT_Shortcoder' , 'auto_load'),10);
        }

        if (function_exists('vc_add_shortcode_param')) {
            vc_add_shortcode_param('toggle', array( 'BBT_Shortcoder' , 'bbt_toggle_vc_option'));
            vc_add_shortcode_param('multiple_select', array( 'BBT_Shortcoder' , 'bbt_multiple_vc_option'));
            vc_add_shortcode_param('image_selector', array( 'BBT_Shortcoder' , 'bbt_image_selector'));
            vc_add_shortcode_param('image_preview', array( 'BBT_Shortcoder' , 'bbt_image_preview'));
            vc_add_shortcode_param('bbt_icons' , array( 'BBT_Shortcoder' , 'bbt_icon_field'));
            vc_add_shortcode_param('slider', array( 'BBT_Shortcoder' , 'bbt_slider_vc_option'));
        }
    }

    public static function auto_load(){
        if(!empty(self::$config_location)) {
            self::$config = include self::$config_location;
            self::get_shortcodes_only_config();
            self::generate_shortcodes();
        }
        add_action( 'media_buttons' , array( 'BBT_Shortcoder' , 'add_shortcode_button'), 11);
        add_action( 'admin_footer' , array( 'BBT_Shortcoder' , 'add_inline_popup_content' ) );
        add_action( 'admin_enqueue_scripts' , array( 'BBT_Shortcoder' , 'add_scripts' ) );
        add_action( 'wp_enqueue_scripts' , array( 'BBT_Shortcoder' , 'add_wp_scripts' ) );
    }


    private static function generate_shortcodes(){
        $shortcodes = self::$shortcodes_only;
        foreach ($shortcodes as $shortcode_id => $shortcode) {
            if(empty($shortcode['custom_post']))	//if not registered using custom-posts fw part
                add_shortcode( BBT_PREFIX . $shortcode_id , array( 'BBT_Shortcoder' , 'shortcode_view' ) );
        }
    }

    public static function shortcode_view($atts,$content = null,$tag){
        $shortcodes = self::$shortcodes_only;
        foreach ($shortcodes as $shortcode_id => $shortcode) {
            if(BBT_PREFIX . $shortcode_id == $tag){
                $default_atts = array ();
                if(!empty($shortcode['atts']) || !empty($shortcode['add_class'])){
                    if(!empty($shortcode['add_class'])) {
                        $shortcode['atts']['add_class']	=	array(
                            'type'			=>	'text',
                            'title'			=>	esc_html__('Add CSS class','bbt_fw_plugin'),
                            'description'	=>	esc_html__('Custom css class that will be added to the element.','bbt_fw_plugin')
                        ) ;
                    }
                    foreach ($shortcode['atts'] as $att_id => $att)
                        if(is_array($att['type'])){	//if group of atts
                            foreach ($att['type'] as $att_id_secondary => $att_secondary)
                                $default_atts[$att_id_secondary] = isset($att_secondary['default']) ? $att_secondary['default'] : '' ;
                        }else
                            $default_atts[$att_id] = isset($att['default']) ? $att['default'] : '' ;
                }
                $data 				= 	shortcode_atts( $default_atts , $atts );
                $data['content'] 	= 	!empty($shortcode['unautop']) ? do_shortcode(shortcode_unautop($content)) : do_shortcode($content);
                $data['shortcode'] 	= 	$shortcode;
                $data['atts']		=	$atts;
                return self::$load->view($shortcode['view'],$data,true,true);
            }
        }
    }

    public static function add_shortcode_button(){
        if(file_exists(get_template_directory() . 'theme_config/shortcodes-options.php')) :
            $admin_page = get_current_screen();
            if( !empty($admin_page) && $admin_page->base === 'post'){
                add_thickbox();
                echo '<a href="#" id="shortcoder" class="sc-button radius-5">
					<span class="button-label radius-5 bg-dark" data-title="' . esc_html__("Shortcodes","bbt_fw_plugin") . '">
						<i class="icon-brand"></i>
					</span>
					<span class="button-text">' . esc_html__("Shortcodes","bbt_fw_plugin") . '</span>
				</a>';
            }
        endif;
    }

    public static function add_inline_popup_content(){
        $admin_page = get_current_screen();
        if( !empty($admin_page) && $admin_page->base === 'post'){
            self::$load->view('shortcoder',self::$config);
        }
    }

    public static function add_scripts($hook){
        if ( !in_array($hook, array('post.php','post-new.php'))) {
            return;
        }
        $protocol = is_ssl() ? 'https' : 'http';
        wp_enqueue_style( 'bbt-g-font', $protocol . "://fonts.googleapis.com/css?family=Open+Sans:400,700|Montserrat:400,700");
        wp_enqueue_style( 'bbt-fa-css', BBT_FW . '/static/css/font-awesome.min.css' );
        wp_enqueue_style( 'bbt-font-material', BBT_FW . '/static/css/font-material.css' );
        wp_enqueue_style( 'bbt-select2-css', BBT_FW . '/static/css/select2.css' );
        wp_enqueue_style( 'bbt-shortcoder-css', BBT_FW . '/static/css/shortcoder.css' );
        wp_enqueue_style( 'bbt-metadata-css', BBT_FW . '/static/css/metadata.css' );
        wp_enqueue_style( 'bbt-admin-css', BBT_FW . '/static/css/admin.css' );
        wp_enqueue_style( 'bbt-chosen.min-css', BBT_FW . '/static/css/chosen.min.css' );
        wp_enqueue_script( 'bbt-bootstrap-js', BBT_FW . '/static/js/bootstrap3.min.js' , array('jquery','jquery-ui-accordion'), false, true );
        wp_enqueue_script( 'bbt-select2-js', BBT_FW . '/static/js/select2.min.js' , array('jquery'), false, true );
        wp_enqueue_script( 'bbt-vc_extension-js', BBT_FW . '/static/js/vc_extension.js' , array('jquery'), false, true );
        wp_enqueue_script( 'bbt-chosen.jquery.min-js', BBT_FW . '/static/js/chosen.jquery.min.js' , array('jquery'), false, true );
        wp_enqueue_script( 'bbt-shortcoder-js', BBT_FW . '/static/js/shortcoder.js' , array('bbt-bootstrap-js','wp-color-picker'), false, true );
        wp_enqueue_style( 'wp-color-picker' );
        wp_localize_script( 'bbt-shortcoder-js', 'php_vars', array(
            'prefix'	=>	BBT_PREFIX,
            'content'	=>	esc_html__('Your Content Here','bbt_fw_plugin')
        ) );
    }

    public static function add_wp_scripts(){
        wp_enqueue_script( 'bbt-bbt-framework-js', BBT_FW . '/static/js/bbt-framework.js' , array('jquery'), false, true );
    }

    public static function get_attr( $att ){
        self::$load->view('shortcode_atts/' . $att['type'],$att);
    }

    public static function get_attributes( $atts , $in_group = false , $force_multiple = false){

        foreach($atts as $att_id => $att) :
            if(is_array($att['type'])) {
                echo '<li class="atts-group-container"><ul class="sc-options">' ;
                $force_multiple_local = !empty($att['multiple']) ? true : false;

                BBT_Shortcoder::get_attributes( $att['type'] , true , $force_multiple_local);
                if (!empty($att['multiple']))
                    echo "<a href='#' class='att-group-add-more sc-btn radius-50 btn-red-out'>Add More</a>";
                echo '</ul></li>' ;
                continue;
            }?>
            <li class="attribute-container sc-field" data-att="<?php echo esc_attr($att_id ) ; if (!empty($att['multiple']) || $force_multiple) echo '_1'?>" data-att-type="<?php echo esc_attr( $att['type'] ); ?>">
                <div class="sc-label"><?php if(!empty($att['title'])) print $att['title'] ?></div>
                <div class="sc-input-box">
                    <?php $att['att_id']=$att_id;BBT_Shortcoder::get_attr($att) ;?>
                    <?php if(!empty( $att['description'])): ?>
                        <span class="help-icon">?</span>
                        <p class="att-description"><?php print $att['description'] ?></p>
                    <?php endif; ?>
                </div>
                <?php if (!empty($att['multiple']) && !$in_group)
                    echo "<a href='#' class='att-add-more sc-btn radius-50 btn-red-out'>Add More</a>";?>
            </li>
        <?php endforeach;
    }

    public static function vc_insert_sc(){
        $options = self::$config;
        $shortcodes = array();
        if(!empty($options['groups'])){
            foreach ($options['groups'] as $group_id => $group){
                foreach($group['shortcodes'] as $shortcode_id => $shortcode){
                    $params = array();
                    if(!empty($shortcode['atts']) && empty($shortcode['not_vc'])){
                        foreach($shortcode['atts'] as $att_id => $att){
                            if(!is_array($att['type'])){	//no blocks of parameters
                                $find_type = self::find_type($att);
                                $type = $find_type[0];
                                $value = $find_type[1];
                                if(!empty($att['multiple'])){	//multiple single parameter
                                    for($i=1;$i<=5;$i++){
                                        $params[] = array(
                                            "type" => $type,
                                            "holder" => "div",
                                            "class" => "",
                                            "heading" => '',
                                            "param_name" => $att_id . "_" . $i,
                                            "value" => '',
                                            "description" => ''
                                        );
                                        $params[] = array(
                                            "type" => 'end_multiple',
                                            "holder" => "div",
                                            "class" => array($i,$att_id,1),
                                            "heading" => '',
                                            "param_name" => "end_multiple_" . $i,
                                            "value" => '',
                                            "description" => ''
                                        );
                                    }
                                    vc_add_shortcode_param('end_multiple', array( 'BBT_Shortcoder' , 'end_multiple') , BBT_FW . '/static/js/vc.js' );
                                }else{
                                    $params[] = array(
                                        "type"			=> $type,
                                        "holder"		=> "div",
                                        "class"			=> "",
                                        "heading"		=> $att['title'],
                                        "param_name"	=> $att_id,
                                        "value"			=> $value,
                                        "settings"		=> !empty($att['settings']) ? $att['settings'] : '',
                                        "dependency"	=> !empty($att['dependency']) ? $att['dependency'] : '',
                                        'std'			=> !empty($att['default']) ? $att['default'] : '',
                                        "description"	=> !empty($att['description']) ? $att['description'] : '',
                                    );
                                }
                            }else{ //multiple blocks of parameters

                                vc_add_shortcode_param('end_multiple', array( 'BBT_Shortcoder' , 'end_multiple') , BBT_FW . '/static/js/vc.js' );

                                for($i=1;$i<=5;$i++){
                                    foreach ($att['type'] as $att_multiple_id => $att_multiple) {
                                        $find_type = self::find_type($att_multiple,true);
                                        $type = $find_type[0];
                                        $value = $find_type[1];
                                        if(!empty($att['multiple'])){
                                            $params[] = array(
                                                "type" => $type,
                                                //"holder" => "iframe",
                                                "class" => '',
                                                "heading" => !empty($att_multiple['title']) ? $att_multiple['title'] : '',
                                                "param_name" => $att_multiple_id . "_" . $i,
                                                "value" => $value,
                                                //'std'	=>	!empty($att_multiple['default']) ? $att_multiple['default'] : '',
                                                "description" => !empty($att_multiple['description']) ? $att_multiple['description'] : '',
                                            );
                                        }
                                    }
                                    $params[] = array(
                                        "type" => 'end_multiple',
                                        "holder" => "div",
                                        "class" => array($i,$att_id,count($att['type'])),
                                        "heading" => '',
                                        "param_name" => "end_multiple_" . $i,
                                        "value" => '',
                                        "description" => ''
                                    );
                                }
                            }
                        }
                        $container_array = (!isset($shortcode['container']) || ($shortcode['container'] == false) || ($shortcode['container'] == '') ) ? array('content_element' => false, "is_container"	=>	false ) : array("content_element"	=>	$shortcode['container'] , "is_container"	=>	$shortcode['container'] );
                        $vc_description = isset($shortcode['vc_desc']) ? $shortcode['vc_desc'] : $shortcode['description'];
                        vc_map( array(
                            "name" => $shortcode['title'],
                            "description" => $vc_description,
                            "base" => BBT_PREFIX . $shortcode_id,
                            $container_array,
                            "show_settings_on_create"	=>	isset($shortcode['show_settings']) ? $shortcode['show_settings'] : '',
                            "category" => BBT_THEME_PRETTY_NAME . " - " . (file_exists(get_template_directory() . '/theme_config/bbt_map.php') ? "Deprecated" : $group['title']),
                            "params" => $params,
                            "icon" => !empty($shortcode['vc_icon']) ? $shortcode['vc_icon'] : BBT_FW . '/static/img/bbt.png',
                        ) );
                    }
                }
            }
        }
    }

    public static function end_multiple($settings, $value){
        return '<button class="bbt-vc-add-more vc_btn-primary vc_btn" data-block-index="' . $settings['class'][0] . '" data-block="' . $settings['class'][1] . '" data-param-in-block="' . $settings['class'][2] . '">Add More</button><hr>';
    }

    static function find_type($att,$multiple = false){
        $value="";
        $options = !empty($att['options']) ? $att['options'] : '';
        switch ($att['type']) {
            case 'text':
                $type = 'textfield';
                break;
            case 'image':
                $type = 'attach_image';
                break;
            case 'color':
                $type = 'colorpicker';
                break;
            case 'select':
                $type = 'dropdown';
                if($multiple)
                    $value = array('Choose an option' => '') + $options;
                else
                    $value = $options;
                break;
            case 'select_with_tooltip':
                $type = 'dropdown';
                $value = array_keys($options);
                break;
            case 'radio':
                $type = 'dropdown';
                $value = $options;
                break;
            case 'icon':
                $type = 'iconpicker';
                break;
            default:
                $type = $att['type'];
                $value = '';
                break;
        }
        return array($type,$value);
    }

    /*
    Add Range Option to Visual Composer Params
    */

    public static  function bbt_slider_vc_option($settings, $value)
    {
        $dependency = '';
        $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
        $type       = isset($settings['type']) ? $settings['type'] : '';
        $min        = isset($settings['min']) ? $settings['min'] : '';
        $max        = isset($settings['max']) ? $settings['max'] : '';
        $step       = isset($settings['step']) ? $settings['step'] : '';
        $unit       = isset($settings['unit']) ? $settings['unit'] : '';
        $default_value = isset($settings['value']) ? $settings['value'] : '';
        $value       = !empty($value) ? $value : $default_value;

        $uniqID    = uniqid();
        $output     = '';
        $output .= '<div class="bbt_slider_wrap" >
			<div ' . $dependency . ' class="mk-range-input ' . $dependency . '" data-value="' . $value . '" data-min="' . $min . '" data-max="' . $max . '" data-step="' . $step . '" id="rangeInput-' . $uniqID . '"></div>
			<input name="' . $param_name . '"  class="bbt_input_selector wpb_vc_param_value ' . $param_name . ' ' . $type . '" type="text" value="' . $value . '"/>
			<span class="unit">' . $unit . '</span></div>';
        $output .= '<script type="text/javascript">

			jQuery("#rangeInput-' . $uniqID . '") .bbtSliderVcOption();

		</script>';

        return $output;
    }

    public static  function bbt_toggle_vc_option($settings, $value)
    {
        $dependency = '';
        $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
        $type       = isset($settings['type']) ? $settings['type'] : '';
        $output     = '';
        $uniqID     = uniqid();

        if(is_array($value)) {
            foreach ($value as $key => $val) {
                $value = $key;
            }
        }

        $value = ($value) ? $value : 'false';

        $output .= '<span class="bbt_toggle mk-composer-toggle" id="toggle-switch-' . $uniqID . '">
		<span class="toggle-handle"></span>
			<input type="hidden" ' . $dependency . ' class="wpb_vc_param_value ' . $dependency . ' ' . $param_name . ' ' . $type . '" value="' . $value . '" name="' . $param_name . '"/>
		</span>';

        $output .= '<script type="text/javascript">

			jQuery("#toggle-switch-' . $uniqID . '").bbtToggleVcOption();

		</script>';

        return $output;
    }

    public static  function bbt_multiple_vc_option($settings, $value)
    {
        $dependency = '';
        $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
        $type       = isset($settings['type']) ? $settings['type'] : '';
        $options    = isset($settings['options']) ? $settings['options'] : '';
        $limit    = isset($settings['limit']) ? $settings['limit'] : '';
        $output     = '';
        $uniqID    = uniqid();

        $max_select = (!empty($limit)) ? '{max_selected_options: '.$limit.'}' : '';

        $output .= '<select multiple="multiple" name="' . $param_name . '" id="multiselect-' . $uniqID . '" style="width:100%" ' . $dependency . ' class="wpb-multiselect ' . $dependency . ' wpb_vc_param_value ' . $param_name . ' ' . $type . '">';
        if ($options != null && !empty($options)) {
            foreach ($options as $key => $option) {
                $selected = '';
                if (in_array($key, explode(',', $value))) {
                    $selected = ' selected="selected"';
                }
                $output .= '<option value="' . $key . '"' . $selected . '>' . $option . '</option>';
            }
        }
        $output .= '</select>';

        $output .= '<script type="text/javascript">

		jQuery("#multiselect-' . $uniqID . '").chosen('.$max_select.');

		</script>';

        return $output;
    }

    public static  function bbt_image_selector($settings, $value)
    {
        $dependency = '';
        $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
        $border     = isset($settings['border']) ? $settings['border'] : '';
        $type       = isset($settings['type']) ? $settings['type'] : '';
        $options    = isset($settings['value']) ? $settings['value'] : '';
        $output     = '';
        $uniqID    = uniqid();

        $border_css = ($border == 'true') ? 'border:1px solid #ddd;' : '';

        if(is_array($value)) {
            foreach ($value as $key => $val) {
                $value = $key;
            }
        }

        $output .= '<div class="bbt_multiple" id="visual-selector' . $uniqID . '">';
        foreach ($options as $key => $option) {
            $output .= '<a style="margin:10px 10px 0 0;' . $border_css . '" href="#" rel="' . $option . '"><img  src="' . get_template_directory_uri() . '/theme_config/sc_screens/client/' . $key . '" /></a>';
        }
        $output .= '<input name="' . $param_name . '" id="' . $param_name . '" ' . $dependency . ' 
		class="wpb_vc_param_value ' . $dependency . ' ' . $param_name . ' ' . $type . '" 
		type="hidden" value="' . $value . '"/>';
        $output .= '</div>';

        $output .= '<script type="text/javascript">

			bbtImageSelector(' . ');

		</script>';

        return $output;
    }

    public static  function bbt_image_preview($settings, $value)
    {
        $dependency = '';
        $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
        $border     = isset($settings['border']) ? $settings['border'] : '';
        $type       = isset($settings['type']) ? $settings['type'] : '';
        $options    = isset($settings['value']) ? $settings['value'] : '';
        $output     = '';
        $uniqID    = uniqid();

        $border_css = ($border == 'true') ? 'border:1px solid #ddd;' : '';

        $output .= '<div class="bbt_multiple" id="visual-selector' . $uniqID . '">';
        foreach ($options as $key => $option) {
            $output .= '<img  src="' . get_template_directory_uri() . '/theme_config/sc_screens/client/' . $key . '" />';
        }
        $output .= '<input name="' . $param_name . '" id="' . $param_name . '" ' . $dependency . ' 
		class="wpb_vc_param_value ' . $dependency . ' ' . $param_name . ' ' . $type . '" 
		type="hidden" value="' . $value . '"/>';
        $output .= '</div>';

        return $output;
    }


    public static  function bbt_icon_field($settings, $value)
    {
        $dependency = '';
        $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
        $type = isset($settings['type']) ? $settings['type'] : '';
        $class = isset($settings['class']) ? $settings['class'] : '';
        $icons = myGlobals::$bbt_custom_vc_icons;
        $icons_md = isset(myGlobals::$bbt_md_vc_icons) ? myGlobals::$bbt_md_vc_icons : '';
        $uniqID    = uniqid();

        if(is_array($value)) {
            foreach ($value as $key => $val) {
                $value = $key;
            }
        }

        $output = '<input type="text" name="search_icon" class="search_md_icon" value="" id="search-'. $uniqID .'" style="width: 510px; margin-bottom: 15px; padding-right: 25px;" />
		<i class="fa fa-search" style="position:relative; left:-25px;"></i>
		<input type="hidden" name="'.$param_name.'" class="wpb_vc_param_value ' . $dependency . ' '.$param_name.' '.$type.' '.$class.'" ' . $dependency . ' value="'.$value.'" id="trace-'. $uniqID .'"/>';
        $output .='<div id="icon-dropdown" >';
        $output .= '<ul class="bbt-icon-list">';
        $n = 1;
        foreach($icons as $icon)
        {
            $selected = ($icon == $value) ? 'class="selected"' : '';
            $id = 'icon-'.$n;
            $output .= '<li '.$selected.' data-ico="'.$icon.'"><i class="icon-'.$icon.'"></i><label class="icon">'.$icon.'</label></li>';
            $n++;
        }
        if(!empty($icons_md))
            foreach($icons_md as $icons_md)
            {
                $selected = ($icons_md == $value) ? 'class="selected"' : '';
                $id = 'icon-'.$n;
                $output .= '<li '.$selected.' data-ico="'.$icons_md.'"><i class="mi-icon">'.$icons_md.'</i><label class="icon">'.$icons_md.'</label></li>';
                $n++;
            }

        $output .='</ul>';
        $output .='</div>';
        $output .= '<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery("#search-'. $uniqID .'").keyup(function(){
				 
						// Retrieve the input field text and reset the count to zero
						var filter = jQuery(this).val(), count = 0;
				 
						// Loop through the icon list
						jQuery(".bbt-icon-list li").each(function(){
				 
							// If the list item does not contain the text phrase fade it out
							if (jQuery(this).text().search(new RegExp(filter, "i")) < 0) {
								jQuery(this).fadeOut();
							} else {
								jQuery(this).show();
								count++;
							}
						});
					});
				});

				jQuery("#icon-dropdown li").click(function() {
					var $input_hiddent = jQuery(this).parents("#icon-dropdown").siblings("input");
					jQuery(this).attr("class","selected").siblings().removeAttr("class");
					var icon = jQuery(this).attr("data-ico");
					$input_hiddent.attr("value", icon);
					jQuery(".icon-preview").html("<i class=\'icon "+icon+"\'></i><label>"+icon+"</label>");
				});
		</script>';
        return $output;
    }
}