# mybb-theme-bridge

A bidirectional filesystem to database bridge for developing MyBB 1.8 themes.  

## Settings

The following are configurable as Environment Variables:

```
MYSQL_HOST
MYSQL_USER
MYSQL_PASSWORD
MYSQL_DATABASE
```

## Usage

`php tpl.php dump`

Dumps template files from the database to the filesystem

`php tpl.php remove`

Watch the template files on the filesystem and update the db on change.

`php tpl.php sync`

Updates the filesystem templates in the database.

## License

Unclear: https://github.com/manmohanjit1/mybb_watch/issues/2