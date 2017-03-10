<?php

class BBT_WP_IMPORTER extends BBT_WP_Import {
    /** @var string Url to install folder, where are all imported files (images, uploads, etc.) */
    public $install_dir = '';
    /** @var string Url to imported xml */
    public $upload_url_old = '';
    
    public $menu_items_info = array();

    public $xmlfile = '';

	//get all minor error messages and show the in popup
	public $minor_error_msg = '';

	protected $finish_msg = '';

    function __construct()
    {
        parent::__construct();
    }

	//include main import template
	function bbt_start_importing($demo_path) {
		$this->install_dir = $demo_path;

		$install_info = $this->dispatch();

		return $install_info;
	}

    //include main import template
    function header() {

    }

    //first import function wich is called
    function dispatch() {
	    //holds all notices messages from xml import
	    $notice_messages = '';

	    $info = $this->bbt_install_attachments();

	    /*if(empty($info['id'])){
		    return array('install' => 'no', 'message' => $info['message'], 'notices' => '');
	    }*/

	    $this->fetch_attachments = true;
	    $this->id  = !empty($info['id']) ? (int)$info['id'] : '';

	    $file = get_attached_file($this->id);

	    set_time_limit(0);

	    //call import function
	    //in $result can be an error so we will display it
		$this->import($file);

	    //if error display it
	    /*if ( is_wp_error( $result ) )
		    return array( 'install' => 'no', 'message' => $result->get_error_message(), 'notices' => '' );
	    elseif(isset($result['notices']))
		    $notice_messages = $result['notices'];*/

	    //echo footer in any case
        $this->footer();

	    //return array( 'install' => 'yes', 'message' => $this->finish_msg, 'notices' => $notice_messages );
    }

    //echo footer
    function footer() {
       echo '';
    }

    /**
     * replace old urls with new ones
     *
     * @param string $item array value
     */
    function fly_url_replace($item)
    {
        if(!is_array($item))
        {
            if (strpos($item, $this->upload_url_old) !== false)
                return  str_replace($this->upload_url_old, home_url(), $item);
            else
                return $item;
        }
        else
            return $item;
    }

    /**
    * Copy a file, or recursively copy a folder and its contents
    * @param       string   $source    Source path
    * @param       string   $dest      Destination path
    * @param       string   $permissions New folder creation permissions
    * @return      bool     Returns true on success, false on failure
    */
    function xcopy($source, $dest)
    {


        // Simple copy for a file 
        if (bbt_check_external_file($source) == 200) {
            copy($source, $dest);
            return chmod($dest, 0755);
        }

        // Make destination directory 
        if (!is_dir($dest)) { 
            mkdir($dest); 
        }

        chmod($dest, 0755);

        // Loop through the folder 
        $dir = dir($source); 
        while (false !== $entry = $dir->read()) { 
            // Skip pointers 
            if ($entry == '.' || $entry == '..') { 
                    continue; 
            } 

            // Deep copy directories 
            if ($dest !== "$source/$entry") { 
                    $this->xcopy("$source/$entry", "$dest/$entry"); 
            } 
        } 

        // Clean up 
        $dir->close(); 
        return true; 
    }

	/**
	 * bbt_install_attachments
	 * import all attachments
	 * @return  attachements id to import all wordpress content
	 */
	function bbt_install_attachments() {
		$error = false;

		if(bbt_check_external_file($this->install_dir . '/demo.xml') == 200){
			$filename = $this->install_dir . '/demo.xml';
			//change framework link in wordpress xml
			//$this->fly_change_wordpress_xml_links($filename);

			//load wordpress export file xml
			$xmlDoc = simplexml_load_file($filename);

			//Use that namespace
			$namespaces = $xmlDoc->getNameSpaces(true);

			//get old site url
			if(!empty($xmlDoc->channel->link))
				foreach((Array)$xmlDoc->channel->link as $old_url)
					$this->upload_url_old = $old_url;

			// call the function
			$args = array( 'file' => $filename);

			$defaults = array( 'file' => '');
			$args = wp_parse_args( $args, $defaults );

			if ( bbt_check_external_file( $args['file'] ) == 200) {

				// for windows systems
				$file = str_replace( '\\', '/', $args['file'] );

				$this->xmlfile = $file;
			}
			else
			{
				esc_html_e('File not found!', 'bbt_fw_plugin');
			}

			//return;

			$uploads = wp_upload_dir();

			$url = $uploads['basedir'] .'/'. basename( $this->xmlfile );
			$type = 'application/xml'; // we know the mime type of our file
			$file = basename( $this->xmlfile );
			$filename = basename( $this->xmlfile );

			// Construct the object array
			$object = array( 'post_title' => $filename,
			                 'post_content' => $url,
			                 'post_mime_type' => $type,
			                 'guid' => $url,
			                 'context' => 'import',
			                 'post_status' => 'private'
			);

			// Save the data
			$id = wp_insert_attachment( $object, $file );
			//copy all attachments in uplods directory
			//$file_copy = $this->xcopy($this->install_dir . '/uploads',  $uploads['basedir']);
			//copy xml file in uploads directory

			$install_file_copy = $this->xcopy($this->xmlfile , $uploads['basedir'] .'/'.basename( $this->xmlfile ));
			if(!$install_file_copy)
			{
				esc_html_e('Upload error!', 'bbt_fw_plugin');
			}

			// schedule a cleanup for one day from now in case of failed import or missing wp_import_cleanup() call
			wp_schedule_single_event( time() + DAY_IN_SECONDS, 'importer_scheduled_cleanup', array( $id ) );

			return array( 'file' => $file, 'id' => $id , 'message' => '');
		}

		$error = new WP_Error( 'import_file_error' );
		if ( is_wp_error( $error ) ) {
			echo 'The import file could not be found at <code>'.$this->install_dir .'import_files </code>.';
		}
	}
}

