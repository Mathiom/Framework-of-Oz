<?php //#########################################
// Quickly create i18n custom post types (CPT)
//###############################################
class Framework_of_Oz_CPT{
	public $cpt;	//A copy of what the user used to create the CPT
	public $cpts;	//Collection of loaded cpts

	function __construct($cpt){
		if(!is_admin()) return;
		$this->cpt 	= $cpt;
		
		add_action('init', array(&$this, 'create'));
	}

	//===============================================
	// Create the CPT
	//===============================================
	function create(){
		global $oz;
		$cpt = $this->cpt;
		//===============================================
		// Convert $cpt from a string to array
		// :: Ensure 'id' arg is present
		//===============================================
		if(is_string($cpt)){
			parse_str($cpt, $args);

			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Only passed slug
			//- - - - - - - - - - - - - - - - - - - - - - - -
			if(count($args) == 1 && !$args[key($args)]){
				$cpt = array('id' => key($args));
			} else
				$cpt = $args;
		}
		if(!isset($cpt['id'])) trigger_error('Creating a CPT without an ID [' . print_r($cpt, true) . ']');

		//===============================================
		// Set Defaults
		//===============================================
		$id = $oz->deslug($cpt['id']);
		$singular = $oz->def($cpt['singular'], 	$id);
		$plural = 	$oz->def($cpt['label'],	 	$id);
					$oz->def($cpt['labels'], 	array());
					$oz->def($cpt['mp6-icon'], 	'');
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Labels Defaults
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$oz->def($cpt['labels']['name'], 				$plural);
		$oz->def($cpt['labels']['singular_name'], 		$singular);
		$oz->def($cpt['labels']['menu_name'],			$plural);
		$oz->def($cpt['labels']['all_items'],			'All ' . $plural);
		$oz->def($cpt['labels']['add_new'],				'Add New');
		$oz->def($cpt['labels']['add_new_item'],		'Add New ' . $singular);
		$oz->def($cpt['labels']['edit_item'],			'Edit ' . $singular);
		$oz->def($cpt['labels']['new_item'],			'New ' . $singular);
		$oz->def($cpt['labels']['view_item'],			'View ' . $singular);
		$oz->def($cpt['labels']['search_items'],		'Search ' . $plural);
		$oz->def($cpt['labels']['not_found'],			'No ' . $plural . ' found');
		$oz->def($cpt['labels']['not_found_in_trash'],	'No ' . $plural . ' found in Trash');
		$oz->def($cpt['labels']['parent_item_colon'],	'Parent ' . $singular);

		register_post_type($cpt['id'], $cpt);
	}
}

//###############################################
// Public Methods
//###############################################
class Framework_of_Oz_CPT_Public{
	//- - - - - - - - - - - - - - - - - - - - - - - -
	// Properties
	//- - - - - - - - - - - - - - - - - - - - - - - -
	static $instance;	//Our singleton instance
	public $metaboxes;	//Collection of loaded metaboxes

	//===============================================
	// Instantiate Singleton
	//===============================================
	static function singleton(){
		if(self::$instance === null)
			self::$instance = new Framework_of_Oz_CPT_Public;
		return self::$instance;		
	}

	//===============================================
	// Create a new metabox
	// $mb 		(ARR) Metabox Data
	//===============================================
	function create($mb){
		global $oz;
		$oz->_metaboxes++;
		$metaboxes[$oz->_metaboxes] = new Framework_of_Oz_CPT($mb);
		return $metaboxes[$oz->_metaboxes];
	}
}