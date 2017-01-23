<?php
/**
* Demo Content Import Class
*
* The Demo Content Import Class is used to import your demo xml files into your database
*
*/

if ( ! class_exists( 'BBT_Demo_Import') ) :

/**
*
* @package BBT Demo Import
* @since 1.0
*
* @todo Nothing.
*/

class BBT_Demo_Import{

	protected $load;

	/**
	 * Demo Content Import page Id.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0
	 */
	protected $pageID = 'bbt_demo_content';

	/**
	 * SelfPath to allow themes as well as plugins.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0
	 */
	protected $selfPath;
	protected $selfPathDir;

	/**
	 * All configurations from existing demos
	 *
	 * @var array
	 * @access protected
	 * @since 1.0
	 */
	protected $demoConfigs;

	public function __construct(){
		// If we are not in admin area exit.
		if ( ! is_admin())
			return;

		//load BBT_Load class methods
		$this->load = new BBT_Load;
		$this->selfPath = plugin_dir_url( __FILE__ );
		$this->selfPathDir = BBT_PL_DIR . 'demo-import/';

		//get demos configurations
		$this->demoConfigs = $this->bbt_get_demos_configurations();

		//add submenu page in bbt admin
		add_action( 'admin_menu',array( $this, 'bbt_register_import_page'));
		// Load demo content import scripts
		add_action( 'admin_print_styles', array( $this, 'load_scripts_styles' ) );

		//call import function on ajax request
		add_action('wp_ajax_bbt_make_import' ,array( $this, 'bbt_make_import'));
		add_action('wp_ajax_nopriv_bbt_make_import',array( $this, 'bbt_make_import'));
	}

	/**
	 * bbt_register_import_page
	 * add import page in admin
	 * @access public
	 */
	public function bbt_register_import_page() {
		//$parent_page = defined('THEME_SMALL_NAME') ? THEME_SMALL_NAME . '_settings' : 'bigbangthemes_settings';

		if(defined('BBT_PL_DIR')) {
			if(defined('BBT_THEME_PRODUCT_KEY') && BBT_THEME_PRODUCT_KEY) {
				add_submenu_page(
					'bbt_welcome_theme',
					esc_html__('BBT Demo Content', 'BigBangThemesFramework'),
					esc_html__('BBT Demo Content', 'BigBangThemesFramework'),
					'administrator',
					$this->pageID,
					array(&$this, 'bbt_main_view_page')
				);
			}
			else {
				add_theme_page('BBT Demo Content', esc_html__('BBT Demo Content', 'bbt_fw_plugin'), 'manage_options', $this->pageID, array(&$this, 'bbt_main_view_page'));
			}
		}
	}

	/**
	 * bbt_main_view_page
	 * function added to add_menu_page hook - the demo import extension template view
	 * @access public
	 */
	public function bbt_main_view_page(){
		if(file_exists( BBT_PL_DIR . '/demo-import/views/demo-import.php' ))
		{
			echo bbt_plugin_view('demo-import', 'demo-import', array('configs' => $this->demoConfigs), TRUE, NULL);
		}

	}

	/**
	 * Load all Javascript and CSS
	 *
	 * @since 1.0
	 * @access public
	 */
	public function load_scripts_styles() {
		// Get Plugin Path
		$plugin_path = plugin_dir_url( __FILE__ );

		//check if current page is demo content import
		if ($this->pageID == 'bbt_demo_content'){

			// Enqueue Main Style
			wp_enqueue_style( 'bbt-demo-import-style', $plugin_path . '/css/demo-import-style.css' );

			// Enqueue Meta Box Scripts
			wp_enqueue_script( 'bbt-demo-import-script', $plugin_path . '/js/demo-import-script.js', array( 'jquery' ), null, true );
		}
	}

	/**
	 * bbt_get_demos_configurations
	 * function that get all demos configurations from theirs config.php files
	 * @access protected
	 * @return array - array of configurations
	 */
	protected  function bbt_get_demos_configurations(){
		$base_demo_url = 'http://bigbangthemes.net/themes-repo/demo_imports/';
		$config_file = $base_demo_url . BBT_THEME_NAME .'/demos_config.php';

		$response = wp_remote_get( $config_file , array('method' => 'POST', 'timeout' => 100, 'body' => array( 'request' => true )));
		$demos = array();
		if( !is_wp_error( $response ) && is_array($response) ) {
			$demos = json_decode($response['body']);
		}

		//array of cofigurations
		$configs = array();
		if(!empty($demos)){
			$cnt = 0;
			foreach($demos as $demo){
				$demo_path = $base_demo_url . BBT_THEME_NAME . '/' . $demo;

				//check if in demo exist demo.xml file
				if(bbt_check_external_file($demo_path . '/demo.xml') != 200) continue;

				//demo path
				$configs['demo-' . md5($demo_path)] = array('demo_path' => $demo_path);

				//check if in demo exist config file
				if(bbt_check_external_file($demo_path . '/config.php') == 200){
					$request = wp_remote_get( $demo_path . '/config.php' , array('method' => 'POST', 'timeout' => 100, 'body' => array( 'request' => true )) );

					if( !is_wp_error( $request ) && is_array($request) ) {
						$config = (array)json_decode($request['body']);

						//get demo title
						$configs['demo-' . md5($demo_path)]['title'] = (isset($config['title']) && !empty($config['title'])) ? $config['title'] : esc_html__('Demo Title','bbt_fw_plugin');
						//get demo screenshot
						$configs['demo-' . md5($demo_path)]['screenshot'] = (isset($config['screenshot']) && !empty($config['screenshot'])) ? $config['screenshot'] : '';
						//get demo preview_link
						$configs['demo-' . md5($demo_path)]['preview_link'] = (isset($config['preview_link']) && !empty($config['preview_link'])) ? $config['preview_link'] : '';
					}
				}

				$cnt++;
			}
		}

		return $configs;
	}


	/**
	 * fly_make_import
	 *
	 * @access public
	 */
	public function bbt_make_import() {

		//check if install id exists
		if(isset($_POST['install_id']))
		{
			if(empty($_POST['install_id'])){
				echo json_encode(array('install' => 'no', 'message' => esc_html__('Missing Install','bbt_fw_plugin')));
				die();
			}

			//get clicked demo path
			$demo_path = (array_key_exists($_POST['install_id'], $this->demoConfigs)) ? $this->demoConfigs[$_POST['install_id']]['demo_path'] : array();
			
			if(empty($demo_path)){
				echo json_encode(array('install' => 'no', 'message' => esc_html__('No Demo Path','bbt_fw_plugin')));
				die();
			}

			// include importer file parsers
			if ( ! defined( 'WP_LOAD_IMPORTERS' ) )
				define('WP_LOAD_IMPORTERS', true);

			require_once $this->selfPathDir . 'libs/wordpress-importer.php';
			require_once $this->selfPathDir . 'libs/bbt_wp_importer_class.php';

			$bbt_wp_import = new BBT_WP_IMPORTER();
			//start import
			$install_info = $bbt_wp_import->bbt_start_importing($demo_path);
			$install_info = array_merge($install_info, array('home_url' => home_url('/') ));

			echo json_encode($install_info);
		}
		else
		{
			echo json_encode(array('install' => 'no', 'message' => esc_html__('Some configurations are missing','bbt_fw_plugin')));
		}

		die();
	}
}

endif;