<?php //#########################################
// Metaboxes
//###############################################

//###############################################
// Controls Metaboxes
//###############################################
class Framework_of_Oz_Metabox{
	public $mb;		//A copy of the original metabox
	public $meta;	//The post meta for this metabox
	public $postID; //The current post ID
	public $postCPT;	//The current posts CPT
	public $isMenupage;	//Whether we are in a menupage or posttype

	function __construct($mb){
		if(!is_admin()) return;
		$this->mb = $mb;

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Actions
		//- - - - - - - - - - - - - - - - - - - - - - - -
		add_action('add_meta_boxes', array(&$this, 'prepare'));
		if(!$this->isMenupage = isset($mb['page']))
			add_action('save_post', array(&$this, 'save'));
	}

	//===============================================
	// Sanitizes the Metabox
	// :: RETURNS (BOOL) whether this metabox belongs to thigs post
	//===============================================
	function sanitize(){
		global $oz;

		//===============================================
		// Defaults
		//===============================================
		$oz->def($this->mb['label'], 		$oz->deslug($this->mb['id']));
		$oz->def($this->mb['context'], 		'normal');
		$oz->def($this->mb['priority'], 	'default');
		$oz->def($this->mb['include-ids'], 		array());
		$oz->def($this->mb['exclude-ids'], 		array());
		$oz->def($this->mb['only-ids'], 		array());
		$oz->def($this->mb['templates'], 		array());

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Explode posts to array
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if(is_integer($this->mb['include-ids'])) $this->mb['include-ids'] = array($this->mb['include-ids']);
		if(is_string($this->mb['include-ids'])) $this->mb['include-ids'] = explode(',', $this->mb['include-ids']);
		if(is_integer($this->mb['exclude-ids'])) $this->mb['exclude-ids'] = array($this->mb['exclude-ids']);
		if(is_string($this->mb['exclude-ids'])) $this->mb['exclude-ids'] = explode(',', $this->mb['exclude-ids']);
		if(is_integer($this->mb['only-ids'])) $this->mb['only-ids'] = array($this->mb['only-ids']);
		if(is_string($this->mb['only-ids'])) $this->mb['only-ids'] = explode(',', $this->mb['only-ids']);

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Get Post Types / Templates
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$this->get_cpts();
		if(is_string($this->mb['templates'])) $this->mb['templates'] = explode(',', $this->mb['templates']);

		return $this->validate();
	}

	//===============================================
	// Checks if we are in the correct screen
	//===============================================
	function validate(){
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Get the current Post Type
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$screen = get_current_screen();
		$cpt = $screen->post_type;
		$this->postCPT = $cpt;
		if(!$this->isMenupage) $template = get_post_meta($this->postID, '_wp_page_template', true);

		//===============================================
		// MenuPages
		//===============================================
		if($this->isMenupage){
			if($cpt == $this->mb['page']) {
				add_action('load-'.$cpt, array(&$this, 'save_options'));
				return true;
			}
		//===============================================
		// ONLY include list
		//===============================================
		} elseif(count($this->mb['only-ids'])) {
			if(count($this->mb['templates']) && !in_array($template, $this->mb['templates'])) return;
			if(!in_array($this->postID, $this->mb['only-ids'])) return false;
		//===============================================
		// Normal CPTs
		//===============================================
		} else {
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Does not have template
			//- - - - - - - - - - - - - - - - - - - - - - - -
			if(count($this->mb['templates']) && !in_array($template, $this->mb['templates'])) return;
			if($template && !in_array($template, $this->mb['templates'])) return;
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// In exclude list
			//- - - - - - - - - - - - - - - - - - - - - - - -
			if(in_array($this->postID, $this->mb['exclude-ids'])) return false;
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Not in post type && Note in includes list
			//- - - - - - - - - - - - - - - - - - - - - - - -
			if(!in_array($cpt, $this->mb['post-types']) && !in_array($this->postID, $this->mb['include-ids'])) return;
			elseif(!in_array($cpt, $this->mb['post-types'])) return;
		}
		return true;
	}

	//===============================================
	// Prepare the Metabox
	//===============================================
	function prepare(){
		global $oz;
		global $current_screen;

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Get the post ID
		//- - - - - - - - - - - - - - - - - - - - - - - -	
		if(!($postID = $oz->admin_post_id()) && $current_screen->action != 'add' && $current_screen->post_type) return false;
		$this->postID = $postID;
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Get the page
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if($this->isMenupage)
			if(!isset($_GET['page']) || $_GET['page'] !== $this->mb['page']) return false;

		//===============================================
		// Validate Input
		//===============================================
		if(isset($this->mb['id']) && !is_string($this->mb['id'])) return trigger_error('Metabox "id" must be a string. Currently is [' . $this->mb['id'] . ']');
			elseif(!isset($this->mb['id'])) return trigger_error('"id" is required to create metabox');
		if(isset($this->mb['fields']) && !is_array($this->mb['fields'])) return trigger_error('"fields" must be an array for [' . $this->mb['id'] . ']');
			elseif(!isset($this->mb['fields'])) return trigger_error('"fields" are required to create metabox for [' . $this->mb['id'] . ']');

		//===============================================
		// Add page as post type
		//===============================================
		if($this->isMenupage)
			$this->mb['post-types'][] = $this->mb['page'];

		//###############################################
		// Attach Metaboxes
		//###############################################
		if(!$this->sanitize()) return false;
		add_meta_box($this->mb['id'], $this->mb['label'], array(&$this, 'initialize_metabox'), $this->postCPT, $this->mb['context'], $this->mb['priority']);
	}

	//===============================================
	// Get the list of CPTs this metabox displays on
	//===============================================
	function get_cpts(){
		global $oz;
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Set default
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$oz->def($this->mb['post-types'], array('*'));
		$this->mb['post-types'] = $this->mb['post-types'];			

		//===============================================
		// Include/Exclude CPT's if wildcard is present
		//===============================================
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Convert 'post-types' to an array
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if(isset($this->mb['post-types']) && !is_array($this->mb['post-types']))
			$this->mb['post-types'] = explode(',', $this->mb['post-types']);
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Add each registered post type
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if(in_array('*', $this->mb['post-types'])){
			$cpts = get_post_types();
			if(!$cpts) 
				return false;

			$myCPTS = $this->mb['post-types'];
			$this->mb['post-types'] = array();
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Include all
			//- - - - - - - - - - - - - - - - - - - - - - - -
			if(count($myCPTS) == 1){
				foreach($cpts as $cpt)
					$this->mb['post-types'][] = $cpt;
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Exclude Some
			//- - - - - - - - - - - - - - - - - - - - - - - -
			} else{
				foreach($cpts as $cpt)
					if(array_search($cpt, $myCPTS) === false)
						$this->mb['post-types'][] = $cpt;
			}
		}	
		return $this->mb['post-types'];
	}

	//===============================================
	// Initialize the metabox: Load scripts etc
	//===============================================
	function initialize_metabox($post){
		global $oz;
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Enqueue Scripts and Styles
		//- - - - - - - - - - - - - - - - - - - - - - - -
		wp_enqueue_media();
		wp_enqueue_style('framework-of-oz-styles', $oz->__FILE__ . '/styles/style.css');
		wp_enqueue_script('framework-of-oz-metaboxes', $oz->__FILE__ . '/js/metaboxes.js', array('jquery'));
		wp_enqueue_script('jquery-ui-sortable');

        //- - - - - - - - - - - - - - - - - - - - - - - -
		// Load Data
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if($this->isMenupage)
			$this->mb['meta'] = get_option($this->mb['page'] . '-' . $this->mb['id']);
		else
			$this->mb['meta'] = get_post_meta($this->postID, $this->mb['id'], true);

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Place the Metabox
		//- - - - - - - - - - - - - - - - - - - - - - - -		
		wp_nonce_field('save_metabox', 'oz_metabox');
		echo '<div class="oz-metabox">';
			foreach($this->mb['fields'] as $key=>$field){
				$this->display_field($field, $key);
			}
		echo '</div>';
	}

	//===============================================
	// Display a field
	//
	// $fields 	(ARR) List of fields to recurse through	
	// $key 	(STR/INT) The fields key
	// $parents (ARR) List of parents
	// $groupID (INT) The current groups ID (for repeats)
	//===============================================
	private function display_field($field, $key, $parents = array(), $groupID = 0){
		global $oz;
		$parentID = implode('-', $parents) . (count($parents) ? '-' : '');

		//===============================================
		// Sanitize the field
		//===============================================
		$this->field_to_array($field, $key);
		extract($field);
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Groups
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if($type == 'group') {
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Setup group counter values
			//- - - - - - - - - - - - - - - - - - - - - - - -
			$parentID = $field['id'];
			$this->field_to_array($field, $key, true);
			$value = $oz->def($this->mb['meta'][$parentID], 1);
			$id = $this->mb['id'] . '-' . $parentID;
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Toggled states
			//- - - - - - - - - - - - - - - - - - - - - - - -
			$states = json_decode($oz->def($this->mb['meta'][$parentID . '---states'], "[]"));

			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Create the Group Panel
			//- - - - - - - - - - - - - - - - - - - - - - - -
			array_push($parents, $parentID);
			echo "<div class='oz-group-wrap'>
			<input type='text' id='$id---states' name='$id---states' class='oz-group-states hidden' value='",json_encode($states),"'>
			<input type='text' id='$id' name='$id' class='oz-group-counter hidden' value='$value'>";
				//===============================================
				// Loop for each panel
				//===============================================
				for($i = 0; $i < $value; $i++):
					//- - - - - - - - - - - - - - - - - - - - - - - -
					// Repalce tags in handle label and set state
					//- - - - - - - - - - - - - - - - - - - - - - - -
					$actuaLabel = str_replace('%count%', $i+1, $label);
					$state = '';
					if(isset($states[$i]) && $states[$i])
						$state = 'closed';

					//===============================================
					// The Handle/Panel items
					//===============================================
					echo "<div class='oz-group-pair'><div class='oz-group-handle' data-original-label='$label'>
							<span>$actuaLabel</span>
							<div class='oz-group-repeat-wrap'>
								<button class='button oz-group-add'>+</button>
								<button class='button oz-group-remove'>-</button>
							</div>
						</div>
						<div class='oz-group-panel $state'>";

							//===============================================
							// Add each field to the group
							//===============================================
							foreach($field['fields'] as $key=>$childField)
								$this->display_field($childField, $key, $parents, $i);
					echo '</div></div>';
				endfor;
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Close it
			//- - - - - - - - - - - - - - - - - - - - - - - -
					array_pop($parents);
			echo '</div>';
			return false;
		}

		//===============================================
		// Setup additional attributes
		//===============================================
		if(is_string($atts)) parse_str($atts, $atts);
		$attString = '';	//Contains the actual string to use
		foreach($atts as $key=>$att){
			$attString .= $key . '="'.$att.'"';
		}
		$atts = $attString;

		//===============================================
		// Get the value
		//===============================================
		$value = $oz->def($this->mb['meta'][$parentID . $id], '');
	
		//===============================================
		// Set other attributes
		//===============================================
		$id 	= $this->mb['id'] . '-' . $parentID . $id;
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Update $id to include groupID
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if(count($parents)){
			$id .= '[' . $groupID . ']';
			$value = isset($value[$groupID]) ? $value[$groupID] : '';
		}
		$name 	= $id;

		//===============================================
		// Create the label and field wrapper
		//===============================================
		echo "<label class='oz-label ", $stack ? 'stacked' : '',"' for='$id'>$label</label>",
			"<div class='oz-field-wrap ",$stack ? 'stacked' : '', $repeat ? ' oz-repeat ': '', "'>";

			//###############################################
			// Loop for repeats
			//###############################################
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Determine how many times to loop
			//- - - - - - - - - - - - - - - - - - - - - - - -
			$repeatLoops = 1;
			if($repeat){
				if(is_array($value)) $repeatLoops = max(count($value), 1);
				else{
					$value = array('');
					$repeatLoops = 1;
				}
			}
			$valueBak = $value;	//Backup value
			$nameBak = $name;	//Backup name
			$idBak = $id;		//Backup ID
			//===============================================
			// Loop once for each repeat (or just once if not a repeater)
			//===============================================
			for($i = 0; $i < $repeatLoops; $i++):
				//- - - - - - - - - - - - - - - - - - - - - - - -
				// Setup current repeat attributes
				//- - - - - - - - - - - - - - - - - - - - - - - -
				$value 	= $valueBak;
				$name 	= $nameBak;
				$id 	= $idBak;
				if($repeat) {
					$name .= '[' . $i . ']';
					$id .= '[' . $i . ']';
					if(count($valueBak)) $value = $valueBak[$i];
				}

				//- - - - - - - - - - - - - - - - - - - - - - - -
				// Repeater Wrap
				//- - - - - - - - - - - - - - - - - - - - - - - -
				echo '<div class="oz-field-repeat-wrap">';

					//===============================================
					// Create the field
					//===============================================
					switch($oz->def($type, 'text')){
						//===============================================
						// Text & Password
						//===============================================
						case 'text': case 'password':
							$value = esc_attr($value);
							echo "<input id='$id' class='$class' name='$name' type='$type' value='$value' $atts>";
						break;
						//===============================================
						// Selectbox
						//===============================================
						case 'select':
							//- - - - - - - - - - - - - - - - - - - - - - - -
							// Convert possible values to array
							//- - - - - - - - - - - - - - - - - - - - - - - -
							if(is_string($field['options'])) parse_str($field['options'], $field['options']);
							if(is_integer($field['options'])) $field['options'] = array($field);

							echo "<select id='$id' class='$class' name='$name' $atts>";
								foreach($field['options'] as $optionVal=>$optionLabel){
									echo "<option value='$optionVal' ",($value == $optionVal ? "selected='selected'" : ''),">$optionLabel</option>";
								}
							echo '</select>';
						break;
						//===============================================
						// File Upload
						//===============================================
						case 'file':
							$value = esc_attr($value);
							echo "<input id='$id' class='$class width-three-quarters' name='$name' type='text' value='$value' $atts>";
							echo "<div><button type='button' data-filetypes='",$filetypes,"' class='button width-quarter'>$button</button></div>";
							echo "<div class='oz-preview'></div>";
						break;
						//===============================================
						// Editor
						//===============================================
						case 'editor':
							wp_editor($value, $id, array(
								'textarea_name'	=> $name,
								'editor_class' => $class
							));				
						break;
						//===============================================
						// Submit
						//===============================================
						case 'submit':
							echo "<input id='$id' class='$class button-primary' type='$type' value='$button' $atts>";							
						break;
						default: trigger_error('Field type "' . $field['type'] . '" does not exist for field[' . $field['id'] . '] in metabox[' . $this->mb['id'] . ']');
					}

					//===============================================
					// Add Repeater Buttons
					//===============================================
					if($repeat){
						echo '<div class="oz-repeaters">
							<button type="button" class="oz-repeater-add button">+</button><button type="button" class="oz-repeater-remove button">-</button>
						</div>';
					}
					echo '<div class="oz-description">',esc_html($desc),'</div>';
				echo '</div>';	//.oz-field-wrap

				//- - - - - - - - - - - - - - - - - - - - - - - -
				// Errors
				//- - - - - - - - - - - - - - - - - - - - - - - -
				if(isset($field['id']) && !is_string($field['id'])) return trigger_error('Field "id" must be a string for metabox['.$this->mb['id'].']');
					elseif(!isset($field['id'])) return trigger_error('"id" is required to create metabox');
			endfor;
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Close Divs
		//- - - - - - - - - - - - - - - - - - - - - - - -
		echo '</div>';	//.oz-field-wrap
	}

	//===============================================
	// Sanitizes a field by converting it into an array
	// and setting defaults.
	//
	// $field 		(&$field) The field, by reference
	// $key 		($key) The fields key
	// $parents
	// $noDefaults 	(BOOL) Whether we should skip default (true) or set them (false).
	//					This should be set to true when saving to speed up the process
	//===============================================
	private function field_to_array(&$field, $key, $noDefaults = false){
		global $oz;
		//===============================================
		// Convert into an array
		//===============================================
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// 'type1' => array('id' => 'type1')
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if(is_integer($key) && is_string($field))
			$field = array('id' 	=> $field);

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// 'type2' => array('id' => 'type2', 'type' => 'editor')
		// :: 'type3' => array('id' = 'type3', parse_str(STR))
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if(is_string($field)){
			parse_str($field, $atts);
			//- - - - - - - - - - - - - - - - - - - - - - - -
			// If it was just a string, then autoconvert it to 'type'
			//- - - - - - - - - - - - - - - - - - - - - - - -
			if(count($atts) == 1 && reset($atts) == null)
				$atts = array('type' => key($atts));

			$atts['id'] = $key;
			$field = $atts;
		}
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// 'type4' => just turn the key into an id
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if(is_string($key) && is_array($field))
			$field['id'] = $key;

		//===============================================
		// Set defaults
		//===============================================
		$oz->def($field['type'], 		'text');
		$oz->def($field['fields'], 		array());
		if($noDefaults) return;

		$oz->def($field['label'], 		$oz->deslug($field['id']));
		$oz->def($field['button'], 		'Media Library');
		$oz->def($field['desc'], 		'');
		$oz->def($field['atts'], 		'');
		$oz->def($field['filetypes'], 	'image');
		$oz->def($field['stack'], 		'');
		$oz->def($field['cap'], 		'manage_options');
		$oz->def($field['repeat'], 		false);
		$oz->def($field['options'], 	array());
		$field['class'] = $oz->def($field['class'], '') . ' oz-field oz-' . $field['type']. ' ';		
	}

	//###############################################
	// Save
	//###############################################
	function save($postID){
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Validate credentials
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if(!is_user_logged_in()) return $postID;
		if(!isset($_POST['oz_metabox']) || !wp_verify_nonce($_POST['oz_metabox'], 'save_metabox'))
			return $postID;
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return $postID;
		if(wp_is_post_revision($postID)) return false;

		//===============================================
		// Save only if post meets requirements
		//===============================================
		$this->postID = $postID;
		if(!$this->sanitize()) return false;

		//===============================================
		// Go through each $_POST object and try to save
		//===============================================
		$save = array();
		$flat = array();
		$flat = $this->flatten($this->mb['fields'], $flat);
		foreach($_POST as $key=>$value){
			if(isset($flat[$key])){
				$keyName = preg_replace('/'.$this->mb['id'] . '\-'.'/', '', $key, 1);
				$save[$keyName] = $value;
			}
		}

		update_post_meta($postID, $this->mb['id'], $save);
	}

	//###############################################
	// Save Options
	//###############################################
	function save_options(){
		global $current_user;

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Validate credentials
		//- - - - - - - - - - - - - - - - - - - - - - - -
		if(!is_user_logged_in()) return add_action('admin_notices', array(&$this, 'notice_not_logged_in'));
		if(isset($_POST['oz_metabox']) && !wp_verify_nonce($_POST['oz_metabox'], 'save_metabox'))
			return add_action('admin_notices', array(&$this, 'notice_invalid_nonce'));
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			return false;

		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Compare the page being saved with the one defined
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		if($_GET['page'] != $this->mb['page']) return add_action('admin_notices', array(&$this, 'notice_wrong_page'));

		//===============================================
		// Go through each $_POST object and try to save
		//===============================================
		$save = array();
		$flat = array();
		$flat = $this->flatten($this->mb['fields'], $flat);
		foreach($_POST as $key=>$value){
			if(isset($flat[$key])){
				$keyName = preg_replace('/'.$this->mb['id'] . '\-'.'/', '', $key, 1);
				$save[$keyName] = $value;
			}
		}
		update_option($this->mb['page'] . '-' . $this->mb['id'], $save);
		if($save) add_action('admin_notices', array(&$this, 'notice_saved'));
	}
	//===============================================
	// Messages
	//===============================================
	function notice_not_logged_in(){
		global $oz;
		if(!$oz->def($oz->menupage_noticed['not-logged-in'], false)) echo '<div class="error"><p>You must be logged in!</p></div>';		
		$oz->menupage_noticed['not-logged-in'] = true;
	}
	function notice_invalid_nonce(){
		global $oz;
		if(!$oz->def($oz->menupage_noticed['invalid-nonce'], false)) echo '<div class="error"><p>Our apologies, but your credentials have expired! Please try again.</p></div>';		
		$oz->menupage_noticed['invalid-nonce'] = true;
	}
	function notice_wrong_page(){
		global $oz;
		if(!$oz->def($oz->menupage_noticed['wrong-page'], false)) echo '<div class="error"><p>This is awkward, but we just tried to save to the wrong page! Please try again.</p></div>';
		$oz->menupage_noticed['wrong-page'] = true;
	}
	function notice_saved(){
		global $oz;
		if(!$oz->def($oz->menupage_noticed['saved'], false)) echo '<div class="updated"><p>Changes saved.</p></div>';		
		$oz->menupage_noticed['saved'] = true;
	}

	//===============================================
	// Get a flat list of all fields and their ID's
	//===============================================
	function flatten($fields, &$flat, $parents = array()){
		foreach($fields as $key=>$field){
			$this->field_to_array($field, $key, true);

			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Groups
			//- - - - - - - - - - - - - - - - - - - - - - - -
			if($field['type'] == 'group') {
				array_push($parents, $field['id']);
				$flat[$this->mb['id'] . '-' . implode('-', $parents)] = '';
				$flat[$this->mb['id'] . '-' . implode('-', $parents) . '---states'] = '';
				$this->flatten($field['fields'], $flat, $parents);
				array_pop($parents);
				continue;
			}

			//- - - - - - - - - - - - - - - - - - - - - - - -
			// Add the Field
			//- - - - - - - - - - - - - - - - - - - - - - - -
			$flat[$this->mb['id'] . '-' . implode('-', $parents) . (count($parents) ? '-' : '') . $field['id']] = '';
		}
		return $flat;
	}
}


//###############################################
// Public Methods
//###############################################
class Framework_of_Oz_Metabox_Public{
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
			self::$instance = new Framework_of_Oz_Metabox_Public;
		return self::$instance;		
	}

	//===============================================
	// Create a new metabox
	// $mb 		(ARR) Metabox Data
	//===============================================
	function create($mb){
		global $oz;
		$oz->_metaboxes++;

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Create the metabox
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$metaboxes[$oz->_metaboxes] = new Framework_of_Oz_Metabox($mb);
		return $metaboxes[$oz->_metaboxes];
	}
}