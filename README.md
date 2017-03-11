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

Dump template files from the database to the filesystem:

`php tpl.php dump`

Remove template files from the filesystem:

`php tpl.php remove`

Push filesystem templates into the database:

`php tpl.php sync`

## License

Unclear: https://github.com/manmohanjit1/mybb_watch/issues/2