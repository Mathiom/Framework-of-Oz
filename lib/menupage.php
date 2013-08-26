<?php //#########################################
// Menu Pages
//############################################### 
class Framework_of_Oz_Menupage{
	public $mp;		//The menu page
	public $page; 	//The actual menu page ID, as built by add_*_page

	//=============================================================================
	// Constructor
	// $mp 			(ARR) The first index contains the same values needed for add_menu_page
	//				The second index contains the menu type (add_menu_page, add_themes_page etc) and defaults to 'add_menu_page'
	//=============================================================================
	function __construct($mp){
		global $oz;
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Validate
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if(!$oz->def($mp['id'], false)) trigger_error(__('Menu Page requires an ID', 'framework-of-oz'));

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Set defaults
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$oz->def($mp['title'],  $oz->deslug($mp['id']));
		$oz->def($mp['type'],  	'root');
		$oz->def($mp['menu'], 	$oz->deslug($mp['title']));
		$oz->def($mp['cap'], 	'manage_options');
		$oz->def($mp['content'], '');
		$oz->def($mp['position'], 	34.149 + $oz->_menupages);
		$this->mp = $mp;

		//===============================================
		// Save Metabox
		//===============================================
		$oz->metabox(array(
			'id'	=> 'save-menupage',
			'label'	=> 'Update Settings',
			'page'  => $mp['id'],
			'cap'	=> $mp['cap'],
			'context' => 'side',
			'fields'	=> array(
				'save' => array(
					'label'		=> '',
					'type'		=> 'submit',
					'button'	=> 'Save'
				)
			)
		));

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Create the menu item
		//- - - - - - - - - - - - - - - - - - - - - - - -
		add_action('admin_menu', array(&$this, 'menu'));
		add_filter('metabox', array(&$this, 'output_metaboxes'));
	}

	//===============================================
	// Add the menu item
	//===============================================
	function menu(){
		global $oz;
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Root
		//- - - - - - - - - - - - - - - - - - - - - - - -
		switch($this->mp['type']){
			case 'root':
				$this->page = add_menu_page($this->mp['title'], $this->mp['menu'], $this->mp['cap'], $this->mp['id'], array(&$this, 'page'), null, $this->mp['position']);
			break;
		}

		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Callbacks for this page only
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		add_action('load-' . $this->page, array(&$this, 'actions'), 9);
		add_action('admin_footer-' . $this->page, array(&$this, 'scripts'));
	}

	//==========================================================
	// Footer Scripts
	//==========================================================
	function scripts(){ ?>
		<script>postboxes.add_postbox_toggles(pagenow);</script>
	<?php }

	//==========================================================
	// Load other actions
	//==========================================================
	function actions(){
		do_action('add_meta_boxes_'.$this->page, null);
		do_action('add_meta_boxes', $this->page, null);
 
		add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
 		wp_enqueue_script('postbox'); 		
	}

	//==========================================================
	// Build the page
	//==========================================================
	function page(){ ?>
		 <div class="wrap">
 
			<?php screen_icon(); ?>
 
			<h2><?php echo esc_html($this->mp['title']);?></h2>
 
			<form name="oz-menupage" method="post">  
				<input type="hidden" name="action" value="<?php echo $this->page ?>">
				<?php //==========================================================
				// Save closed metaboxes and their order
				//==========================================================
				wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
				wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
 
				<div id="poststuff">
		
					 <div id="post-body" class="metabox-holder columns-<?php echo get_current_screen()->get_columns() == 1 ? '1' : '2'; ?>"> 
 
						  <div id="post-body-content">
							<?php if($this->mp['content']) call_user_func($this->mp['content']); ?>
						  </div>    
 
						  <div id="postbox-container-1" class="postbox-container">
						        <?php do_meta_boxes('','side',null); ?>
						  </div>    
 
						  <div id="postbox-container-2" class="postbox-container">
						        <?php do_meta_boxes('','normal',null);  ?>
						        <?php do_meta_boxes('','advanced',null); ?>
						  </div>	     					
 
					 </div> <!-- #post-body -->
				
				 </div> <!-- #poststuff -->
 
	      		  </form>			
 
		 </div><!-- .wrap -->	
	<?php }	
}


//###############################################
// Public Methods
//###############################################
class Framework_of_Oz_Menupage_Public{
	//- - - - - - - - - - - - - - - - - - - - - - - -
	// Properties
	//- - - - - - - - - - - - - - - - - - - - - - - -
	static $instance;	//Our singleton instance
	public $menupages;	//Collection of loaded menupages

	//===============================================
	// Instantiate Singleton
	//===============================================
	static function singleton(){
		if(self::$instance === null)
			self::$instance = new Framework_of_Oz_Menupage_Public;
		return self::$instance;		
	}

	//===============================================
	// Create a new metabox
	// $mb 		(ARR) Menupage Data
	//===============================================
	function create($mb){
		global $oz;
		$oz->_menupages++;
		$menupages[$oz->_menupages] = new Framework_of_Oz_Menupage($mb);
		return $menupages[$oz->_menupages];
	}
}