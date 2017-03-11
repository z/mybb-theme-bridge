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
    echo "Need help?\n";
}

if (isset($argv[1])) {
    switch ($argv[1]) {
        case 'dump':
            $mybb_template->dumpTemplates();
            break;
        case 'list':
            $mybb_theme->listThemes();
            break;
        case 'listcss':
            $mybb_theme->listThemeStyleSheets();
            break;
        case 'remove':
            $mybb_template->removeTemplates();
            break;
        case 'sync':
            $mybb_template->syncTemplates();
            break;
        default:
            help();
    }
} else {
    help();
}
exit;