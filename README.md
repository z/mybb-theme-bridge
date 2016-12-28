# mybb_watch

A PHP CLI file to store all MyBB templates as PHP files and update to DB when changed

Settings
--------
You may update the settings in the `tpl.php` file.

     new MyBB_Template([
		'host'=>'DB_HOST',
		'user'=>'DB_USERNAME',
		'pass'=>'DB_PASSWORD',
		'db'=>'DB_NAME',
	], TEMPLATE_ID, DEFAULT_TEMPLATE_ID, MYBB_VERSION);

Usage
-----
`php tpl.php store`

Store all the template files.

`php tpl.php remove`

Remove all the template files.

`php tpl.php watch`

Watch the stored template files for changes and updates the DB.

`php tpl.php`

Syncs local files onto DB.
