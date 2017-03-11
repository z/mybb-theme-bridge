<?php

set_time_limit(0);

require 'MyBB_Template.php';

$mybb_template = new MyBB_Template([
	'host'=> getenv("MYSQL_HOST") ?: "localhost",
	'user'=> getenv("MYSQL_USER") ?: "root",
	'pass'=> getenv("MYSQL_PASSWORD") ?: "",
	'db'=> getenv("MYSQL_DATABASE") ?: "mybb",
], 3, -2, 1810);

function help() {
    echo "Need help?\n";
}

if (isset($argv[1])) {
    switch ($argv[1]) {
        case 'dump':
            $mybb_template->dumpTemplates();
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