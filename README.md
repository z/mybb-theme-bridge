# mybb-theme-bridge

A bidirectional filesystem to database bridge for developing MyBB 1.8 themes.  

## Settings

The following are configurable as Environment Variables:

```
CSS_PATH=./themes
TEMPLATE_PATH=./templates
MYTB_PATH=
MYSQL_HOST=localhost
MYSQL_USER=root
MYSQL_PASSWORD=
MYSQL_DATABASE=mybb
```

## Usage

### Templates

Dump template files from the database to the filesystem:

`php mytb tpl_dump`

Remove template files from the filesystem:

`php mytb tpl_remove`

Push filesystem templates into the database:

`php mytb tpl_sync`

### CSS

Dump stylesheet files from the database to the filesystem:

`php mytb css_dump`

Remove stylesheet files from the filesystem:

`php mytb css_remove`

TODO: Push filesystem stylesheets into the database:

`php mytb css_sync`


## License

Unclear: https://github.com/manmohanjit1/mybb_watch/issues/2