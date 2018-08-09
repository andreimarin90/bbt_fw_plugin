<?php
/**
* Changelog Class
*
* The Changelog Class is used to show your theme latest changes
*
*/
if ( ! class_exists( 'BBT_Changelog') ) :

/**
*
* @package BBT Changelog
* @since 1.0
*
* @todo Nothing.
*/

class BBT_Changelog{

    /**
     * Changelog txt file path.
     *
     * @var string
     * @access protected
     * @since 1.0
     */
    protected $changelog_file;
	/**
	 * Changelog page Id.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0
	 */
	protected $pageID = 'bbt_changelog';

	/**
	 * SelfPath to allow themes as well as plugins.
	 *
	 * @var string
	 * @access protected
	 * @since 1.0
	 */
	protected $selfPath;
	protected $selfPathDir;


	public function __construct($changelog_file){
		// If we are not in admin area exit.
		if ( ! is_admin())
			return;

		if(!class_exists('BBT_Load')) return;

        $this->changelog_file = $changelog_file;
		//load BBT_Load class methods
		$this->load = new BBT_Load;
		$this->selfPath = plugin_dir_url( __FILE__ );
		$this->selfPathDir = BBT_PL_DIR . 'changelog/';

		//add submenu page in bbt admin
		add_action( 'admin_menu',array( $this, 'bbt_register_changelog_page'));
		// Load changelog page scripts
		//add_action( 'admin_print_styles', array( $this, 'load_scripts_styles' ) );

		//call import function on ajax request
		/*add_action('wp_ajax_bbt_make_import' ,array( $this, 'bbt_make_import'));
		add_action('wp_ajax_nopriv_bbt_make_import',array( $this, 'bbt_make_import'));*/
	}

	/**
	 * bbt_register_changelog_page
	 * add changelog page in admin
	 * @access public
	 */
	public function bbt_register_changelog_page() {
		//$parent_page = defined('THEME_SMALL_NAME') ? THEME_SMALL_NAME . '_settings' : 'bigbangthemes_settings';

		if(defined('BBT_PL_DIR')) {
            add_submenu_page(
                'bbt_welcome_theme',
                esc_html__('Changelog', 'BigBangThemesFramework'),
                esc_html__('Changelog', 'BigBangThemesFramework'),
                'administrator',
                $this->pageID,
                array(&$this, 'bbt_main_view_page')
            );
		}
	}

	/**
	 * bbt_main_view_page
	 * function added to add_menu_page hook - the changelog template view
	 * @access public
	 */
	public function bbt_main_view_page(){
		if(file_exists( BBT_PL_DIR . '/changelog/views/changelog.php' )) {
			if(file_exists($this->changelog_file))
			{
                $content = file_get_contents($this->changelog_file);

                if(!empty($content))
                    echo bbt_plugin_view('changelog', 'changelog', array('content' => $content), TRUE, NULL);
			}
		}
	}

	/**
	 * Load all Javascript and CSS
	 *
	 * @since 1.0
	 * @access public
	 */
	/*public function load_scripts_styles() {
		// Get Plugin Path
		$plugin_path = plugin_dir_url( __FILE__ );

		//check if current page is demo content import
		if ($this->pageID == 'bbt_changelog'){

			// Enqueue Main Style
			wp_enqueue_style( 'bbt-changelog-style', $plugin_path . '/css/changelog-style.css' );

			// Enqueue Meta Box Scripts
			//wp_enqueue_script( 'bbt-changelog-script', $plugin_path . '/js/changelog-script.js', array( 'jquery' ), null, true );
		}
	}*/
}

endif;