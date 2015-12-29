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
			self::$load = new BBT_Load;
			if(function_exists('vc_map')){
				add_action('vc_before_init',array( 'BBT_Shortcoder' , 'auto_load'),10);
				add_action('vc_before_init', array( 'BBT_Shortcoder' , 'vc_insert_sc') , 11);
			}else{
				add_action('init',array( 'BBT_Shortcoder' , 'auto_load'),10);
			}
		}
	}

	public static function auto_load(){
		self::$config = include self::$config_location;
		self::get_shortcodes_only_config();
		self::generate_shortcodes();
		add_action( 'media_buttons' , array( 'BBT_Shortcoder' , 'add_shortcode_button'), 11);
		add_action( 'admin_footer' , array( 'BBT_Shortcoder' , 'add_inline_popup_content' ) );
		add_action( 'admin_enqueue_scripts' , array( 'BBT_Shortcoder' , 'add_scripts' ) );
	}

	private static function get_shortcodes_only_config(){
		$options = self::$config;
		$shortcodes = array();
		if(!empty($options['groups'])){
			foreach ($options['groups'] as $group_id => $group) {
				$shortcodes = array_merge($shortcodes,$group['shortcodes']);
			}
			self::$shortcodes_only = $shortcodes;
		}
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
							'title'			=>	esc_html__('Add CSS class','BigBangThemesFramework'),
							'description'	=>	esc_html__('Custom css class that will be added to the element.','BigBangThemesFramework')
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
		$admin_page = get_current_screen();
		if( !empty($admin_page) && $admin_page->base === 'post'){
			add_thickbox();
			echo '<a href="#" id="shortcoder" class="sc-button radius-5">
				<span class="button-label radius-5 bg-dark" data-title="' . esc_html__("Shortcodes","BigBangThemesFramework") . '">
					<i class="icon-brand"></i>
				</span>
				<span class="button-text">' . esc_html__("Shortcodes","BigBangThemesFramework") . '</span>
			</a>';
		}
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
		wp_enqueue_style( 'bbt-select2-css', BBT_FW . '/static/css/select2.css' );
		wp_enqueue_style( 'bbt-shortcoder-css', BBT_FW . '/static/css/shortcoder.css' );
		wp_enqueue_style( 'bbt-metadata-css', BBT_FW . '/static/css/metadata.css' );
		wp_enqueue_script( 'bbt-bootstrap-js', BBT_FW . '/static/js/bootstrap3.min.js' , array('jquery','jquery-ui-accordion'), false, true );
		wp_enqueue_script( 'bbt-select2-js', BBT_FW . '/static/js/select2.min.js' , array('jquery'), false, true );
		wp_enqueue_script( 'bbt-shortcoder-js', BBT_FW . '/static/js/shortcoder.js' , array('bbt-bootstrap-js','wp-color-picker'), false, true );
		wp_enqueue_style( 'wp-color-picker' );
		wp_localize_script( 'bbt-shortcoder-js', 'php_vars', array(
			'prefix'	=>	BBT_PREFIX,
			'content'	=>	esc_html__('Your Content Here','BigBangThemesFramework')
			) );
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

						if(isset($shortcode['container'])) {
							$container_array = (empty($shortcode['container']) || ($shortcode['container']) == 'false' ) ? array('content_element' => false, "is_container"	=>	false ) : array("content_element"	=>	$shortcode['container'] , "is_container"	=>	$shortcode['container'] );
						}

						vc_map( array(
							"name" => $shortcode['title'],
							"base" => BBT_PREFIX . $shortcode_id,
							"content_element" => $shortcode['content'],
							"is_container"	=>	$shortcode['content'],
							$container_array,
							"show_settings_on_create"	=>	$shortcode['show_settings'],
							"category" => THEME_PRETTY_NAME . " - " . $group['title'],
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
}