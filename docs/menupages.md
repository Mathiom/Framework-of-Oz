## Creating Menupages
The syntax is: `$oz->menupage(array(%data))`

Menupages should be created anytime before the `admin_menu` action, and it's perfectly fine to create them outside of any action at all (we do).
When you create a menupage, a "save" metabox is automatically added to the sidebar! To add other metaboxes to this page, simply set that metaboxes `page` attribute to the `id` you set for the menupage.

`%data` takes in the following keys with defaults:  
```php
array(
	'id'			=> #REQUIRED#,			//(STR) The menupages id, should be a slug
	'title'			=> $oz->deslug(%id), 	//(STR) The menupages title
	'type'			=> 'root',				//(STR) The menu type
	'cap'			=> 'manage_options',	//(STR) The capability required to make changes: http://codex.wordpress.org/Roles_and_Capabilities
	'position'		=> 34.149 + %menupages,	//(INT) The position of the menu item. %menupages is the number of menupages loaded so far in order to prevent duplicates. It is suggested to used decimals for the same reason: http://codex.wordpress.org/Function_Reference/add_menu_page
	'content'		=> ''					//(STR) Content to display underneath the title
)
```

### Menu Types
* **root**: Shown as a root-level menu item

## Getting Data
Metaboxes on menupages are stored in the `options` table, and are retrieved through the WordPress tag [get_option](http://codex.wordpress.org/Function_Reference/get_option):  
`$settings = get_option(%pageID-%metaboxID);`

%pageID is the `page` value you set, followed by a hyphen, followed by the metabox `id`.