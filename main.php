<?php

set_time_limit(0);

$mytb_path = realpath(dirname(__FILE__));

require $mytb_path . '/BaseCLI.php';
require $mytb_path . '/MyBB_Template.php';
require $mytb_path . '/MyBB_Theme.php';

$db = [
    'host'=> getenv("MYSQL_HOST") ?: "localhost",
    'user'=> getenv("MYSQL_USER") ?: "root",
    'pass'=> getenv("MYSQL_PASSWORD") ?: "",
    'db'=> getenv("MYSQL_DATABASE") ?: "mybb",
];

$mybb_template = new MyBB_Template($db, 2, -2, 1810);
$mybb_theme = new MyBB_Theme($db, 3, 1, 1.0);

function help() {
    echo "mytb command reference

Themes

theme_list      List themes in the database

Templates

tpl_dump        Dump template files from the database to the filesystem
tpl_remove      Remove template files from the filesystem
tpl_commit      Push filesystem templates into the database

CSS

css_dump        Dump stylesheet files from the database to the filesystem
css_list        List stylesheet files in the database
css_remove      Remove stylesheet files from the filesystem
css_commit      Push filesystem stylesheets into the database
";
}

if (isset($argv[1])) {
    switch ($argv[1]) {
        case 'css_dump':
            $mybb_theme->dumpThemeStyleSheets();
            break;
        case 'css_list':
            $mybb_theme->listThemeStyleSheets();
            break;
        case 'css_remove':
            $mybb_theme->removeThemeStyleSheets();
            break;
        case 'css_commit':
            $mybb_theme->commitThemeStyleSheets();
            break;
        case 'theme_list':
            $mybb_theme->listThemes();
            break;
        case 'tpl_dump':
            $mybb_template->dumpTemplates();
            break;
        case 'tpl_remove':
            $mybb_template->removeTemplates();
            break;
        case 'tpl_commit':
            $mybb_template->commitTemplates();
            break;
        default:
            help();
    }
} else {
    help();
}
exit;