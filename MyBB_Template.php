<?php


class MyBB_Template extends BaseCLI {
	
	protected $sid, $default_sid, $version, $template_path;

	public function __construct(array $db, $sid = 2, $default_sid = -2, $version = 1809)
	{
		$this->connection = new mysqli('p:'.$db['host'], $db['user'], $db['pass'], $db['db']);
		
		if ($this->connection->connect_error) {
			die('Connect Error (' . $this->connection->connect_errno . ') '. $this->connection->connect_error);
		}
		
		$this->sid = $sid;
		$this->default_sid = $default_sid;
		$this->version = $version;
		$this->template_path = getenv("TEMPLATE_PATH") ?: './templates';
	}
	
	public function setID($sid, $default_sid = null)
	{
		$this->sid = $sid;
		if(!is_null($default_sid)) {
			$this->default_sid = $default_sid;
		}
	}
	
	public function dumpTemplates($quiet = false)
	{
		$organized = $this->organizeTemplates();

		$total = 0;

		exec("mkdir -p {$this->template_path}");

		foreach($organized['templates'] as $group => $templates) {
			exec("mkdir -p {$this->template_path}".$group);

			foreach($templates as $title => $template) {
				$total++;
				file_put_contents($this->template_path.'/'.$group.'/'.$title.'.php', $template);
			}
		}

		$total_groups = count($organized['groups']);
		$total_theme = count($organized['theme']);
		if(!$quiet) echo $this->getColoredString("[SUCCESS]", 'green')." Added a total of {$total} ({$total_theme}) files in {$total_groups} groups.".PHP_EOL;
	}
	
	public function organizeTemplates()
	{
		$groups = $this->getTemplateGroups();

		$default = $this->getTemplates($this->default_sid);
		$theme = $this->getTemplates($this->sid);

		$templates = [];

		foreach($default as $tpl) {
			$templates[ $this->findTemplateGroup($tpl['title'], $groups) ][ $tpl['title'] ] = $tpl['template'];
		}
		foreach($theme as $tpl) {
			$templates[ $this->findTemplateGroup($tpl['title'], $groups) ][ $tpl['title'] ] = $tpl['template'];
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
	
	protected function findTemplateGroup($title, $groups)
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
	
	public function removeTemplates($quiet = false)
	{
		exec("rm -rf {$this->template_path}");

		if(!$quiet) echo $this->getColoredString("[SUCCESS]", 'green').' Removed all files.'.PHP_EOL;
	}
	
	public function syncTemplates($quiet = false)
	{
		$total = 0;
		foreach(glob("{$this->template_path}/*", GLOB_ONLYDIR) as $folder) {
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
		$this->checkConnection();

		$stmt->bind_param('ds', $this->sid, $title);
		$result = $stmt->execute();

		$stmt->bind_result($total);
		$stmt->fetch();

		$stmt->close();

		return $result && $total > 0;
	}
	
	public function updateTemplates($title, $template)
	{
		$time = time();

		$stmt = $this->connection->prepare("UPDATE mybb_templates SET template = ?, dateline = ? WHERE title = ? AND sid = ?");
		$this->checkConnection();

		$stmt->bind_param('sdsd', $template, $time, $title, $this->sid);
		$result = $stmt->execute();

		return $result ? $this->connection->affected_rows : false;
	}

	public function insertTemplates($title, $template)
	{
		$time = time();

		$stmt = $this->connection->prepare("INSERT INTO mybb_templates (title, sid, template, version, dateline) VALUES (?, ?, ?, ?, ?)");
		$this->checkConnection();

		$stmt->bind_param('sdsdd', $title, $this->sid, $template, $this->version, $time);
		$result = $stmt->execute();

		return $result ? $this->connection->affected_rows : false;
	}
	
}
