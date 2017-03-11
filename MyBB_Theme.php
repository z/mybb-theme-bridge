<?php


class MyBB_Theme extends BaseCLI {

    protected $tid, $default_tid, $version, $css_path;

    public function __construct(array $db, $tid = 2, $default_tid = -2, $version = 1809)
    {
        $this->connection = new mysqli('p:'.$db['host'], $db['user'], $db['pass'], $db['db']);

        if ($this->connection->connect_error) {
            die('Connect Error (' . $this->connection->connect_errno . ') '. $this->connection->connect_error);
        }

        $this->tid = $tid;
        $this->default_tid = $default_tid;
        $this->version = $version;
        $this->css_path = getenv("CSS_PATH") ?: './themes';
    }

    public function getThemes()
    {
        $templates = [];
        if($result = $this->connection->query("SELECT * FROM mybb_themes")) {
            while($row = $result->fetch_assoc()) {
                $templates[ $row['name'] ] = $row;
            }
        }

        return $templates;
    }

    public function getThemeStyleSheets($tid)
    {
        $templates = [];
        if($result = $this->connection->query("SELECT * FROM mybb_themestylesheets WHERE tid = {$tid}")) {
            while($row = $result->fetch_assoc()) {
                $templates[ $row['name'] ] = $row;
            }
        }

        return $templates;
    }

    public function listThemes() {
        $themes = $this->getThemes();
        foreach($themes as $theme) {
            echo $theme['name'] . " " . $theme['allowedgroups'] . "\n";
        }
    }

    public function listThemeStyleSheets() {
        $themes = $this->getThemeStyleSheets($this->tid);
        foreach($themes as $theme) {
            echo $theme['name'] . " [" . str_replace('|', ', ', $theme['attachedto']) . "]\n";
        }
    }

    public function dumpThemeStyleSheets($quiet = false)
    {
        $stylesheets = $this->getThemeStyleSheets($this->tid);

        $total = 0;
        $theme = "revoxono";

        exec("mkdir -p {$this->css_path}/$theme");

        foreach($stylesheets as $stylesheet => $data) {
            $total++;
            file_put_contents($this->css_path.'/'.$theme.'/'.$stylesheet, $data['stylesheet']);
        }
        
        if(!$quiet) echo $this->getColoredString("[SUCCESS]", 'green')." Dumped a total of {$total}.".PHP_EOL;
    }
    
}