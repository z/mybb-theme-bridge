<?php

set_time_limit(0);

require 'vendor/autoload.php';
require 'MyBB_Template.php';

$mybb_template = new MyBB_Template([
	'host'=> getenv("MYSQL_HOST") ?: "localhost",
	'user'=> getenv("MYSQL_USER") ?: "root",
	'pass'=> getenv("MYSQL_PASSWORD") ?: "",
	'db'=> getenv("MYSQL_DATABASE") ?: "mybb",
], 3, -2, 1810);

if (isset($argv[1])) {
    switch ($argv[1]) {
        case 'dump':
            $mybb_template->store();
            break;
        case 'remove':
            $mybb_template->remove();
            break;
        case 'sync':
            $mybb_template->sync_all();
            break;
        case 'watch':
            $files = new Illuminate\Filesystem\Filesystem;
            $tracker = new JasonLewis\ResourceWatcher\Tracker;
            $watcher = new JasonLewis\ResourceWatcher\Watcher($tracker, $files);

            $listener = $watcher->watch('templates');

            $listener->modify(function ($resource, $path) use ($mybb_template) {
                $mybb_template->sync($path);
            });

            $watcher->start();
            break;
        default:
            echo "Need help?";
    }
}
exit;