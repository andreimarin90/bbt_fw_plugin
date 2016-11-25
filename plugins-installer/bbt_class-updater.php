<?php
if( ! class_exists( 'BBT_Updater' ) ) {
	
	class BBT_Updater {
		
		//var $api_url = "http://localhost/bigbang/showoff/update-theme/";
		var $api_url = "http://bigbangthemes.net/update-theme/";
	
		function __construct( $license_key, $theme_version ) {
	
			$this->license_key = $license_key;
			$this->theme_version = $theme_version;

			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'check_for_update' ) );
			//add_filter( 'upgrader_pre_download', array( $this, 'upgradeFilter' ), 10, 4 );

			//set_site_transient('update_themes', null);
		}
		
		function check_for_update( $transient ) {
			global $wp_filesystem;

			if( empty( $transient->checked ) )  {
				return $transient;
			}

			$curr_theme = wp_get_theme();
			$curr_ver =  $this->theme_version;

			$url = $this->api_url;
			$args = array(
				'method' => 'POST',
				'timeout' => 30,
				'body' => array( 'license_key' => $this->license_key,  'site_url' => get_site_url(), 'theme_name' => bbt_parent_theme_name() )
			);

			$request = wp_remote_post( $url, $args);

			if ( is_wp_error( $request ) ) 
			{
		    	return $transient;
		    }

		    if ( $request['response']['code'] == 200 )
		    {
		    	$data = json_decode( $request['body'] );

		    	if (!empty($data->error) && $data->error == 1)
		    	{
					delete_option("bbt_".THEME_FOLDER_NAME."_license");
					delete_option("bbt_". THEME_FOLDER_NAME ."_valid_key");
		    	}
				else{
					if(version_compare($curr_ver, $data->version, '<'))
					{
						$transient->response[$curr_theme->get_template()] = array(
                            "new_version"	=> 		$data->version,
                            "package"		=>	    $data->download_url,
                            "url"			=>		'http://bigbangthemes.net'
                        );
    
                        // add_action( 'admin_notices', array(&$this, 'update_notice') );
                        update_option("bbt_".THEME_FOLDER_NAME."_remote_ver", $data->version);
					}
					else
					{
						// update_option("getbowtied_".THEME_SLUG."_update_available", 0);
					}
				}
			}

			return $transient;
		}

		function update_notice() {
			//update_option("bbt_".THEME_FOLDER_NAME."_update_available", 1);
		}

		public function upgradeFilter( $reply, $package, $updater ) {
			global $wp_filesystem;

			// return new WP_Error ('test', $updater->skin->theme_info['Name'] );

			// Update GetBowtied Theme
			$theme = wp_get_theme();

			$condition = isset( $updater->skin->theme_info ) && $updater->skin->theme_info['Name'] === $theme['Name'];
			if (  $condition ) 
			{
				$license = get_option ("bbt_".THEME_FOLDER_NAME."_license");
				if ( !$license && empty($license) )
				{
					return new WP_Error( 'no_credentials', sprintf( __( 'To receive automatic updates license activation is required. Please visit <a href="%1$s" target="_blank">Product Activation</a> to activate your theme.', 'BigBangThemesFramework' ), admin_url( 'admin.php?page=bbt_product_key_page' ) ) );
				}
			}

			// Update GetBowtied distributed VisualComposer
			$condition = isset( $updater->skin->plugin ) && $updater->skin->plugin === 'js_composer/js_composer.php';
			if ( (isset( $updater->skin->plugin )) && ( $updater->skin->plugin === 'js_composer/js_composer.php') );
			{
				// $updater->strings['downloading_package_url'] = __( 'Getting download link...', 'GetBowtied' );
				// $updater->skin->feedback( 'downloading_package_url' );

				$updater->strings['dummy_string'] = __( 'Getting the package...', 'js_composer' );
				$updater->skin->feedback( 'dummy_string' );


				$updater->strings['downloading_package_url'] = __( '', 'js_composer' );
				$updater->skin->feedback( 'downloading_package_url' );

				$updater->strings['downloading_package'] = __( '', 'js_composer' );
				$updater->skin->feedback( 'downloading_package_url' );
			}

			return $reply;
		}
	}

}