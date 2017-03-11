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

### CSS

Dump stylesheet files from the database to the filesystem:

`php mytb css_dump`

List stylesheet files in the database:

`php mytb css_list`

Remove stylesheet files from the filesystem:

`php mytb css_remove`

Push filesystem stylesheets into the database:

`php mytb css_commit`

### Templates

Dump template files from the database to the filesystem:

`php mytb tpl_dump`

Remove template files from the filesystem:

`php mytb tpl_remove`

Push filesystem templates into the database:

`php mytb tpl_commit`

### Themes

List themes in the database:

`php mytb theme_list`

## License

Unclear: https://github.com/manmohanjit1/mybb_watch/issues/2