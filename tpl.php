<?php

set_time_limit(0);

require 'vendor/autoload.php';
require 'MyBB_Template.php';

$mybb_template = new MyBB_Template([
	'host'=>'localhost',
	'user'=>'homestead',
	'pass'=>'secret',
	'db'=>'mybb',
], 3, -2, 1809);

if(isset($argv[1])) {
	if($argv[1] == 'store') {
		$mybb_template->store();
		exit;
	}
	if($argv[1] == 'remove') {
		$mybb_template->remove();
		exit;
	}
	if($argv[1] == 'watch') {
		$files = new Illuminate\Filesystem\Filesystem;
		$tracker = new JasonLewis\ResourceWatcher\Tracker;
		$watcher = new JasonLewis\ResourceWatcher\Watcher($tracker, $files);

		$listener = $watcher->watch('templates');

		$listener->modify(function($resource, $path) use($mybb_template) {
			$mybb_template->sync($path);
		});

		$watcher->start();
	}
}
else {
	$mybb_template->sync_all();
	exit;
}