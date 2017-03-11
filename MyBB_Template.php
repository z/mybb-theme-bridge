<?php

class MyBB_Template {
	
	protected $connection, $sid, $default_sid, $version;
	
	protected $backgroundColors = [
		'black' => '40',
		'red' => '41',
		'green' => '42',
		'yellow' => '43',
		'blue' => '44',
		'magenta' => '45',
		'cyan' => '46',
		'light_gray' => '47',
	];
	protected $foregroundColors = [
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37',
	];
	
	public function __construct(array $db, $sid = 2, $default_sid = -2, $version = 1809)
	{
		$db = array_merge([
			'host'=>'localhost',
			'user'=>'root',
			'pass'=>'',
			'db'=>'mybb',
		], $db);
		$db['host'] = str_replace('p:', '', $db['host']);
		
		$this->connection = new mysqli('p:'.$db['host'], $db['user'], $db['pass'], $db['db']);
		
		if ($this->connection->connect_error) {
			die('Connect Error (' . $this->connection->connect_errno . ') '. $this->connection->connect_error);
		}
		
		$this->sid = $sid;
		$this->default_sid = $default_sid;
		$this->version = $version;
	}
	
	public function setID($sid, $default_sid = null)
	{
		$this->sid = $sid;
		if(!is_null($default_sid)) {
			$this->default_sid = $default_sid;
		}
	}
	
	public function setVersion($v)
	{
		$this->version = $v;
	}
	
	public function dumpTemplates($quiet = false)
	{
		$organized = $this->organize();

		$total = 0;

		exec('mkdir -p ./templates');

		foreach($organized['templates'] as $group => $templates) {
			exec('mkdir -p ./templates/'.$group);

			foreach($templates as $title => $template) {
				$total++;
				file_put_contents('templates/'.$group.'/'.$title.'.php', $template);
			}
		}

		$total_groups = count($organized['groups']);
		$total_theme = count($organized['theme']);
		if(!$quiet) echo $this->getColoredString("[SUCCESS]", 'green')." Added a total of {$total} ({$total_theme}) files in {$total_groups} groups.".PHP_EOL;
	}
	
	public function organize()
	{
		$groups = $this->getTemplateGroups();

		$default = $this->getTemplates($this->default_sid);
		$theme = $this->getTemplates($this->sid);

		$templates = [];

		foreach($default as $tpl) {
			$templates[ $this->findGroup($tpl['title'], $groups) ][ $tpl['title'] ] = $tpl['template'];
		}
		foreach($theme as $tpl) {
			$templates[ $this->findGroup($tpl['title'], $groups) ][ $tpl['title'] ] = $tpl['template'];
		}

		return ['templates'=>$templates, 'default'=>$default, 'theme'=>$theme, 'groups'=>$groups];
	}
	
	public function getTemplateGroups()
	{
		$groups = [];
		if($result = $this->connection->query("SELECT * FROM mybb_templategroups")) {
			while($row = $result->fetch_assoc()) {
				$groups[] = $row['prefix'];
			}
		}

		return $groups;
	}
	
	public function getTemplates($sid)
	{
		$templates = [];
		if($result = $this->connection->query("SELECT * FROM mybb_templates WHERE sid = {$sid}")) {
			while($row = $result->fetch_assoc()) {
				$templates[ $row['title'] ] = $row;
			}
		}

		return $templates;
	}
	
	protected function findGroup($title, $groups)
	{
		$result = "ungrouped";

		foreach($groups as $group) {
			if($title == $group || substr($title, 0, strlen($group.'_')) === $group.'_') {
				$result = $group;
				break;
			}
		}

		return $result;
	}
	
	public function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
	{
		$colored_string = "";

		if (isset($this->foregroundColors[$foregroundColor])) {
			$colored_string .= "\033[" . $this->foregroundColors[$foregroundColor] . "m";
		}
		if (isset($this->backgroundColors[$backgroundColor])) {
			$colored_string .= "\033[" . $this->backgroundColors[$backgroundColor] . "m";
		}

		$colored_string .=  $string . "\033[0m";

		return $colored_string;
	}
	
	public function removeTemplates($quiet = false)
	{
		exec('rm -rf ./templates');

		if(!$quiet) echo $this->getColoredString("[SUCCESS]", 'green').' Removed all files.'.PHP_EOL;
	}
	
	public function syncTemplates($quiet = false)
	{
		$total = 0;
		foreach(glob('templates/*', GLOB_ONLYDIR) as $folder) {
			foreach(glob($folder.'/*.php') as $file) {
				$total++;
				$this->sync($file, true);
			}
		}

		if(!$quiet) echo $this->getColoredString("[SUCCESS]", 'green').' Updated all templates in DB.'.PHP_EOL;

		exit;
	}
	
	public function sync($path, $quiet = false)
	{
		$title = basename($path, '.php');
		$template = file_get_contents($path);

		if(!$quiet) echo $this->getColoredString("[{$title}]", 'green')." Template changed.".PHP_EOL;

		if($this->hasTemplates($title)) {
			if(!$quiet) echo $this->getColoredString("[{$title}]", 'green')." Template exists. Updating...".PHP_EOL;
			$result = $this->updateTemplates($title, $template);
		}
		else {
			if(!$quiet) echo $this->getColoredString("[{$title}]", 'green')." Template missing. Inserting...".PHP_EOL;
			$result = $this->insertTemplates($title, $template);
		}

		$result = (int) $result;
		if($result > 0) {
			if(!$quiet) echo $this->getColoredString("[{$title}]", 'green')." ".$this->getColoredString("Success ({$result})", 'light_gray', 'blue').PHP_EOL;
		}
		else {
			if(!$quiet) echo $this->getColoredString("[{$title}]", 'green')." ".$this->getColoredString("Failure ({$result})", 'light_gray', 'red').PHP_EOL;
		}
	}
	
	public function hasTemplates($title)
	{
		$stmt = $this->connection->prepare("SELECT COUNT(*) AS total FROM mybb_templates WHERE sid = ? AND title = ?");
		$this->check();

		$stmt->bind_param('ds', $this->sid, $title);
		$result = $stmt->execute();

		$stmt->bind_result($total);
		$stmt->fetch();

		$stmt->close();

		return $result && $total > 0;
	}
	
	public function check()
	{
		if ($this->connection->connect_error) {
			die('Connect Error (' . $this->connection->connect_errno . ') '. $this->connection->connect_error);
		}
	}
	
	public function updateTemplates($title, $template)
	{
		$time = time();

		$stmt = $this->connection->prepare("UPDATE mybb_templates SET template = ?, dateline = ? WHERE title = ? AND sid = ?");
		$this->check();

		$stmt->bind_param('sdsd', $template, $time, $title, $this->sid);
		$result = $stmt->execute();

		return $result ? $this->connection->affected_rows : false;
	}
	
	/*
	  Source:
	  https://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
	*/

	public function insertTemplates($title, $template)
	{
		$time = time();

		$stmt = $this->connection->prepare("INSERT INTO mybb_templates (title, sid, template, version, dateline) VALUES (?, ?, ?, ?, ?)");
		$this->check();

		$stmt->bind_param('sdsdd', $title, $this->sid, $template, $this->version, $time);
		$result = $stmt->execute();

		return $result ? $this->connection->affected_rows : false;
	}
	
}
