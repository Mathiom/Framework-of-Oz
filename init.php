<?php //#########################################
// Oz Framework
// :: Create i18n post types, taxonomies, admin pages
// and metaboxes. Fast.
//
// :: https://github.com/Mathiom/Framework-of-Oz
//
// :: Requires PHP 5.3+
//###############################################
if(!class_exists('Framework_of_Oz')){
	require dirname(__FILE__) . '/lib/metabox.php';
	require dirname(__FILE__) . '/lib/cpt.php';
	require dirname(__FILE__) . '/lib/menupage.php';

	//###############################################
	// The Global Singleton
	//###############################################
	class Framework_of_Oz{
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Properties
		//- - - - - - - - - - - - - - - - - - - - - - - -
		static $instance;	//Our singleton instance
		public $metabox;	//Public Metabox methods
		public $cpt;		//Public CPT methods
		public $menupage;	//Public Menupage methods
		public $_metaboxes; //Counts the number of metaboxes created to ensure unique ID's (does not dec when mb are deleted)
		public $_cpts;		//Same as $_metaboxes but for Custom Post Types
		public $_menupages;	//Same as $_metaboxes but for menupages
		public $__file__;	//Windows-safe pointer to the current directory
		public $notices;	//List of shown notices, to prevent duplicates

		//===============================================
		// Instantiate Singleton
		//===============================================
		static function singleton(){
			if(self::$instance === null)
				self::$instance = new Framework_of_Oz;
			return self::$instance;
		}
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Constructor
		//- - - - - - - - - - - - - - - - - - - - - - - -
		private function __construct(){
			$this->metabox 	= Framework_of_Oz_Metabox_Public::singleton();
			$this->cpt 		= Framework_of_Oz_CPT_Public::singleton();
			$this->menupage	= Framework_of_Oz_Menupage_Public::singleton();

			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Get the directory, taking into account windows servers
			//- - - - - - - - - - - - - - - - - - - - - - - -
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			    $this->__FILE__ = trailingslashit( str_replace( DIRECTORY_SEPARATOR, '/', str_replace( str_replace( '/', DIRECTORY_SEPARATOR, WP_CONTENT_DIR ), WP_CONTENT_URL, dirname(__FILE__) ) ) );
			} else {
			    $this->__FILE__ = apply_filters( 'cmb_meta_box_url', trailingslashit( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, dirname( __FILE__ ) ) ) );
			}			
		}

		//###############################################
		// Shortcut Methods
		//###############################################
		//===============================================
		// Create Metabox
		//===============================================
		function metabox($mb){
			return $this->metabox->create($mb);
		}
		//===============================================
		// Create CPT
		//===============================================
		function cpt($cpt){
			return $this->cpt->create($cpt);
		}
		//===============================================
		// Create Menupage
		//===============================================
		function menupage($mp){
			return $this->menupage->create($mp);
		}

		//###############################################
		// Helper Functions
		//###############################################
		//=============================================================================
		// Set a default
		//
		// $var 		[*]	The variable, by reference, to set a default value on
		// $default 	[*] The default value to use if the variable is not set
		//=============================================================================
		function def(&$var, $def){
			if(!isset($var) && isset($def)) return $var = $def;
			if(!isset($var) && !isset($def)) return false;
			return $var;
		}

		//===============================================
		// Deslugifies a string, turning hyphens into spaces 
		// and capitalizing words
		//
		// $slug 	[STR]	String to deslugify
		//===============================================
		function deslug($slug){
			return ucwords(str_replace('-', ' ', $slug));
		}

		//===============================================
		// Get the ID of the current post in the Admin
		//===============================================
		function admin_post_id(){
			$postID = false;
			if(isset($_GET['post']))
				$postID = $_GET['post'];
			elseif(isset($_POST['post_ID']))
				$postID = $_POST['post_ID'];
			return $postID;
		}
	}

	//###############################################
	// Finally, instantiate the global singleton
	//###############################################
	global $oz; $oz = Framework_of_Oz::singleton();
}