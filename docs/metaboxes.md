## Creating a Metabox
The syntax is: `$oz->metabox(array(%data))`

Metaboxes should be created anytime before or during the [admin_menu](http://codex.wordpress.org/Plugin_API/Action_Reference/admin_menu) action, which is where the Framework begins the metabox routine, and it's perfectly fine to create them outside of any action at all (we do). 

`%data` takes in the following keys with defaults.  
```php
array(
	'id'			=> #REQUIRED#,			//(STR) The metabox id, should be a slug
	'label'			=> $oz->deslug(%id), 	//(STR) Metaboxes title
	'post-types'	=> '*',					//(STR/ARR) List of post types, by id, to attach this metabox to. 
												//Strings should be comma separated and w/o spaces. 
												//* will show in all post types
												//* + other post types will exclude those, eg '*,page,post' will show EVERYWHERE except pages and posts
	'include-ids'	=> '',					//(INT/ARR) The list of comma separated post ID's to include...regardless of post-type
	'exclude-ids'	=> '',					//(INT/ARR) List of id's to exclude...regardless of post type
	'only-ids'		=> '',					//(INT/ARR) ONLY include these ID's...regardless of post type
	'page'			=> '',					//(STR) The menupage ID to display on. Only 
	'templates'		=> '',					//(INT/ARR) The list of comma separated template filenames for this metabox. So if you have a template named 'about.php', then 'about.php' is the template name.
												//If the template is in a directory inside the themes folder, then the template name includes the directory(-ies). So if you have a template, 'about.php' inside the folder 'templates/', then the template name is 'templates/about.php'
												//If the post has a template assigned, then metabox MUST contain the template in order to show
	'priority'		=> 'default',			//(STR) The priority. Possible == (high, core, default, low) See: http://codex.wordpress.org/Function_Reference/add_meta_box
	'context'		=> 'normal',			//(STR) The context. Possible == (normal, advanced, side). See the link above
	'fields'		=> array(				//(STR/ARR) #REQUIRED# list of fields. See the "Creating Fields" section for more information
		array(
			'type'		=> 'text',				//(STR) The field type. See the listing below
			'label'		=> $oz->deslug(%type), 	//(STR) The string for the fields associated label
			'button'	=> 'Media Library', 	//(STR) Button label
			'desc'		=> '', 					//(STR) Descriptive text shown below the field
			'stack'		=> false,				//(BOOL) Whether we should stack the label above the field. By default, they are arranged horizontally
			'class'		=> '',					//(STR) A space separated list of extra classes to add. They all recieve 'oz-field oz-%type'
			'atts'		=> '', 					//(STR/ARR) A list of additional attributes to use. Can be a parsable string ('key=value&...') or an associative array. Double Quotes will automatically wrap values.
			'filetypes' => 'image',				//(STR) The allowed filetypes for file uploads. Not really sure what other values can be used though...
			'repeat'	=> false,				//(BOOL) Whether this field is a repeater! Groups are automatically built as repeaters
			'cap'		=> %menupage_cap		//(STR) The capability type required to save. Only used in menupages for now as an extra layer of security
			'options'	=> array() 				//(STR/ARR) A parsable string or array of options (LABEL=>VALUE) for select boxes
		)
	)
)
```

### Creating Fields
```php
array(
	'type1',
	'type2'	=> 'editor',
	'type3'	=> 'type=slider&min=0&max=1000',
	'type4'	=> array(...)
)
```
There are 5 ways to create a field, designed to let you create them as quickly as possible.

**type1**  
`'type1'` creates a text field with the id 'type1'. They are then deslugged to create labels, which in this case, would be "Type 1"

**type2**  
`'type2' => 'editor'` will create an editor with the ID 'type1'. Replace `editor` with the field type.

**type3**  
`'type3' => 'type=slider&min=0&max=1000'` will create a slider #type3 with min/max == (0,1000). Simply use & to separate properties

**type4**  
`'type4'' => array(...)` creates a field, #type4, with the properties set in the array. This is the method you will likely use the most and is required if the field has properties which themselves are arrays.

**type5**  
Same as 'type4', only you must manually set an `id`. All the other methods will eventually get converted into an array, and so this is also the quickest.

### Field Types
The following is a list of all the fields, along with a description and a list of the attributes they accept. All fields accept the following properties:  
```
label
desc
default
stack
class
atts
repeat
```

**text**, **password**  
Standard text and password input fields.

**select**
[options] Standard select box. `options` contains the list of options the selectbox contains. Should either be a parsable string or array, where the key is the value and the label is value...
```php
'options' => array(
	'mammal' 	=> 'Cat',
	'sport' 	=> 'Football',
	3.41459 	=> 'pi'
)

//...or...

'options' => 'mammal=Cat&sport=Football&3.41459=Pi'
```

**file**  
[button, filetypes] Media upload textbox and button. Use `button` to change the button and popup label and `filetypes` to set the file type as defined [here](http://wordpress.org/support/topic/using-wps-thickbox-in-a-plugin?replies=17#post-2149133).

**editor**  
Standard WordPress TinyMCE WYSIWYG.

**submit**
[button] Standard submit button. Doesn't take in any data, and is used simply to submit the form.

## Groups
Groups are useful when you need to provide the user with a way to bulk-repeat items. Everytime the user repeats a group, the group will copy over all of its fields (to include any repeater fields!) into a new panel, clear them out, and focus the first field.

Another handy feature of groups is that you can open, close and rearrange them and the framework will remember their states, as if the group panels themselves where metaboxes.

In order to create a group, create a field like you normally would and set it's `type` to 'group': `'type' => 'group'`  
Then add a `fields` key with an array of fields, like you normally would. So:
```php
'slides' => array(
	'type'	=> 'group',
	'label' => 'Slide %count%',
	'fields' => array(
		%FIELDS
	)
)
```
Groups can also contain a label with a `%count%` smart tag. This tag will get replaced by the order number and is updated whenever the user adds, removes, or rearranges the panels.

## Retrieving Meta Data
Each metabox stores all of its data as an array in a meta field using that metaboxes ID. Therefore, the most basic way to retrieve data is to use the native [get_post_meta](http://codex.wordpress.org/Function_Reference/get_post_meta) tag, as follows:  
`$meta = get_post_meta($post->ID, %metaboxID, true);`

`$meta` will be an array containing each field, where each key represents a field ID. So to get the value of a field that has an ID "subtitle", you would use: `$meta['subtitle']`. If 'subtitle' is listed under a group with id 'chapters', then it would be stored as `$meta['chapters-subtitle']`

The one thing to remember, if you're using `get_post_meta`, is the nesting order when groups and repeaters inside groups are stored. Use `print_r` if you are ever unsure of how to extract this data, but essentially each field value becomes an array...where the index is related to the group #.

However the framework, naturally, provides several shortcuts. 

The basic way to retrieve a value is through `$oz->meta(%metaboxID, %fieldID, %echo);`. `%metaboxID` is the ID of the metabox you're calling, `%fieldID` is the fields ID, and `%echo`, which defaults to `FALSE` allows you to echo the value as well (it still returns it). Again, if the field is in a group, prepend %fieldID with the groups ID and a hyphen `%groupID-%fieldID`.

`$oz->meta->set(%metaboxID)` will set all subsequent `$oz->meta` calls to load fields from `%metaboxID`, so now you only need to pass `%fieldID`, allowing you to do the following:  
```php
$oz->meta->set('title');
echo '<h2>', $oz->meta('subtitle'), '</h2>
	<div class="intro">', $oz->meta('intro'), '</div>';
```