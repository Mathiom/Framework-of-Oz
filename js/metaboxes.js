//###############################################
// Controls for Metaboxes
//###############################################
jQuery(function($){
	//###########################################################################
	// Handle file Upload
	//###########################################################################
	$('.oz-metabox').on('click', '.oz-file + div button', function(){
		var $this = $(this);
		var $text = $this.parent().prev();
		var $preview = $this.parent().next();
		var original_send_to_editor = window.send_to_editor;
		tb_show($this.text(), 'media-upload.php?type='+$this.data('filetypes')+'&TB_iframe=true&post_id='+post_ID, false );

		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		// Grab the file URL and pass it to the input box
		//- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
		window.send_to_editor = function(html){
			var url = $(html).attr('href') || $(html).attr('src');
			$text.val(url);
			$preview.html('<img src="' + url + '">');
			$text.trigger('change');
			tb_remove();
			window.send_to_editor = original_send_to_editor;
		}

		return false;
	});
	//- - - - - - - - - - - - - - - - - - - - - - - -
	// Update preview
	//- - - - - - - - - - - - - - - - - - - - - - - -
	$('.oz-metabox').on('change', '.oz-file', function(){
		var $this = $(this);
		var $preview = $this.next().next();
		$preview.html('<img src="' + $this.val() + '">');

		if(!$this.val()) $preview.hide();
		else $preview.show();
	});
	$('.oz-file').trigger('change');

	//###############################################
	// Repeaters
	//###############################################
	//===============================================
	// Duplicate
	//===============================================
	$('.oz-metabox').on('click', '.oz-repeater-add', function(){
		var $this = $(this);
		var $parent = $this.closest('.oz-field-repeat-wrap');
		$parent.clone().insertAfter($parent);

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Clear values
		//- - - - - - - - - - - - - - - - - - - - - - - -
		clear_values($parent.next().children('.oz-field'));
		update_indices($parent.closest('.oz-field-wrap'));
	});
	//===============================================
	// Remove
	//===============================================
	$('.oz-metabox').on('click', '.oz-repeater-remove', function(){
		var $this = $(this);
		var $parent = $this.closest('.oz-field-wrap');
		if($parent.children('.oz-field-repeat-wrap').length > 1) $this.closest('.oz-field-repeat-wrap').remove();
		update_indices($parent.closest('.oz-field-wrap'));
	});
	//===============================================
	// Update Indices
	// $parent		(ELEM) Element to scan
	//===============================================
	function update_indices($parent){
		$parent.children('.oz-field-repeat-wrap').each(function(i){
			$(this).find('.oz-field').first().each(function(){
				var $this = $(this);
				$this.attr('id', $this.attr('id').replace(/(\d+)(?!.*\d)/, i));
				$this.attr('name', $this.attr('name').replace(/(\d+)(?!.*\d)/, i));
			});
		});
	}
	//===============================================
	// Clear Values
	// $parent		(ELEM) Element to scan
	//===============================================
	function clear_values($children){
		$children.each(function(){
			$(this).val('');
		});
	}

	//###############################################
	// Groups
	//###############################################
	//===============================================
	// Add
	//===============================================
	$('.oz-metabox').on('click', '.oz-group-add', function(){
		var $handle = $(this).closest('.oz-group-handle');
		var $panel = $handle.next();
		var $group = $panel.parent();
		var $parent = $panel.closest('.oz-group-wrap');
		var $counter = $parent.find('.oz-group-counter').first();

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Clone
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$group.clone().insertAfter($group);
		$group.next().children('.oz-group-panel').show().find('.oz-field').first().focus();

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Update counter
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$counter.val($parent.children('.oz-group-pair').length);

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Clear fields
		//- - - - - - - - - - - - - - - - - - - - - - - -
		clear_values($group.next().find('.oz-group-panel > .oz-field-wrap > .oz-field-repeat-wrap > .oz-field'));
		update_group_indices($parent);
		$parent.sortable('refresh');
		return false;
	});
	//===============================================
	// Remove
	//===============================================
	$('.oz-metabox').on('click', '.oz-group-remove', function(){
		var $this = $(this);
		var $parent = $this.closest('.oz-group-wrap');

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Remove if not the last one
		// :: Also update teh counter
		//- - - - - - - - - - - - - - - - - - - - - - - -
		var count = $parent.children('.oz-group-pair').length;
		if(count > 1){
			var $pair = $(this).closest('.oz-group-pair');
			$pair.remove();
			$parent.find('> .oz-group-counter').val(count-1);
		} 

		update_group_indices($parent);
		return false;
	});
	//===============================================
	// Update group indices
	//===============================================
	function update_group_indices($parent){
		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Update name and id
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$parent.find('.oz-group-panel').each(function(i){
			$(this).find('.oz-field').each(function(){
				var $this = $(this);
				var match = $this.attr('name').match(/\[.*?\]/);
				var id = $this.attr('name').replace(match[0], '['+i+']');
				$this.attr('id', id);
				$this.attr('name', id);
			});
		});

		//- - - - - - - - - - - - - - - - - - - - - - - -
		// Update tags
		//- - - - - - - - - - - - - - - - - - - - - - - -
		$parent.find('.oz-group-handle').each(function(i){
			var $this = $(this);
			var text = $this.data('original-label');
			$this.children('span').html(text.split('%count%').join(i+1));
		});

		set_states($parent);
	}
	//===============================================
	// Expand/Collapse groups
	//===============================================
	$('.oz-metabox').on('click', '.oz-group-handle', function(){
		var $this = $(this);
		$this.next().slideToggle(250, function(){
			set_states($this.closest('.oz-group-wrap'));
		});
	});
	//===============================================
	// Set states
	// $wrap the .oz-group-wrap to check
	//===============================================
	function set_states($wrap){
		var states = [];
		$wrap.find('> .oz-group-pair > .oz-group-handle').each(function(i){
			states[i] = $(this).next().is(':visible') ? 0 : 1;
		});
		$wrap.children('.oz-group-states').val(JSON.stringify(states));
	}
	//===============================================
	// Sortable Groups
	//===============================================
	$('.oz-group-wrap').sortable({
		items: 	'> .oz-group-pair',
		handle: '.oz-group-handle',
		placeholder: 'oz-group-placeholder',
		start: function(e, ui){
			ui.placeholder.height(ui.item.height());
		},
		stop: function(event, ui){
			var $this = $(this);
			update_group_indices($this);
		}
	});
});