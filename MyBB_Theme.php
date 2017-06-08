<?php


class MyBB_Theme extends BaseCLI {

    protected $tid, $default_tid, $version, $css_path, $theme;

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
        $this->theme = 'revoxono';
    }


    public function dumpThemeStyleSheets($quiet = false)
    {
        $stylesheets = $this->getThemeStyleSheets($this->tid);

        $total = 0;

        exec("mkdir -p {$this->css_path}/$this->theme");

        foreach($stylesheets as $stylesheet => $data) {
            $total++;
            file_put_contents($this->css_path.'/'.$this->theme.'/'.$stylesheet, $data['stylesheet']);
        }

        if(!$quiet) echo $this->getColoredString("[SUCCESS]", 'green')." Dumped a total of {$total}.".PHP_EOL;
    }

    public function getThemes()
    {
        $stylesheets = [];
        if($result = $this->connection->query("SELECT * FROM mybb_themes")) {
            while($row = $result->fetch_assoc()) {
                $stylesheets[ $row['name'] ] = $row;
            }
        }

        return $stylesheets;
    }

    public function getThemeStyleSheets($tid)
    {
        $stylesheets = [];
        if($result = $this->connection->query("SELECT * FROM mybb_themestylesheets WHERE tid = {$tid}")) {
            while($row = $result->fetch_assoc()) {
                $stylesheets[ $row['name'] ] = $row;
            }
        }

        return $stylesheets;
    }

    public function listThemes() {
        $themes = $this->getThemes();
        echo "Name | Allowed Groups\n";
        foreach($themes as $theme) {
            echo $theme['name'] . " " . $theme['allowedgroups'] . "\n";
        }
    }

    public function listThemeStyleSheets() {
        $themes = $this->getThemeStyleSheets($this->tid);
        echo "Name | Attached To\n";
        foreach($themes as $theme) {
            echo $theme['name'] . " [" . str_replace('|', ', ', $theme['attachedto']) . "]\n";
        }
    }

    public function removeThemeStyleSheets($quiet = false)
    {
        exec("rm -rf {$this->css_path}/{$this->theme}");

        if(!$quiet) echo $this->getColoredString("[SUCCESS]", 'green').' Removed all files.'.PHP_EOL;
    }

    public function commitThemeStyleSheets($quiet = false)
    {
        $total = 0;
        foreach(glob("{$this->css_path}/{$this->theme}/*.css") as $file) {
            $total++;
            $this->syncThemeStyleSheetFile($file, true);
        }

        if(!$quiet) echo $this->getColoredString("[SUCCESS]", 'green').' Updated all stylesheets in DB.'.PHP_EOL;

        exit;
    }

    public function syncThemeStyleSheetFile($path, $quiet = false)
    {
        $name = basename($path);
        $stylesheet = file_get_contents($path);

        if(!$quiet) echo $this->getColoredString("[{$name}]", 'green')." ThemeStyleSheet changed.".PHP_EOL;

        if($this->hasThemeStyleSheets($name)) {
            if(!$quiet) echo $this->getColoredString("[{$name}]", 'green')." ThemeStyleSheet exists. Updating...".PHP_EOL;
            $result = $this->updateThemeStyleSheets($name, $stylesheet);
        }
        else {
            if(!$quiet) echo $this->getColoredString("[{$name}]", 'green')." ThemeStyleSheet missing. Inserting...".PHP_EOL;
            $result = $this->insertThemeStyleSheets($name, $stylesheet);
        }

        $result = (int) $result;
        if($result > 0) {
            if(!$quiet) echo $this->getColoredString("[{$name}]", 'green')." ".$this->getColoredString("Success ({$result})", 'light_gray', 'blue').PHP_EOL;
        }
        else {
            if(!$quiet) echo $this->getColoredString("[{$name}]", 'green')." ".$this->getColoredString("Failure ({$result})", 'light_gray', 'red').PHP_EOL;
        }
    }

    public function hasThemeStyleSheets($name)
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) AS total FROM mybb_themestylesheets WHERE tid = ? AND name = ?");
        $this->checkConnection();

        $stmt->bind_param('ds', $this->tid, $name);
        $result = $stmt->execute();

        $stmt->bind_result($total);
        $stmt->fetch();

        $stmt->close();

        return $result && $total > 0;
    }

    public function updateThemeStyleSheets($name, $content)
    {
        $time = time();

        $stmt = $this->connection->prepare("UPDATE mybb_themestylesheets SET stylesheet = ?, lastmodified = ? WHERE name = ? AND tid = ?");
        $this->checkConnection();

        $stmt->bind_param('sdsd', $content, $time, $name, $this->tid);
        $result = $stmt->execute();

        $stylesheets = $this->getThemeStyleSheets($this->tid);

        foreach($stylesheets as $stylesheet) {
            copy("{$this->css_path}/{$this->theme}/{$stylesheet['name']}", "cache/themes/theme{$stylesheet['tid']}/{$stylesheet['name']}");
        }

        return $result ? $this->connection->affected_rows : false;
    }

    public function insertThemeStyleSheets($name, $content)
    {
        $time = time();

        $stmt = $this->connection->prepare("INSERT INTO mybb_themestylesheets (name, tid, stylesheet, lastmodified) VALUES (?, ?, ?, ?)");
        $this->checkConnection();

        $stmt->bind_param('sdsd', $name, $this->tid, $content, $time);
        $result = $stmt->execute();

        $stylesheets = $this->getThemeStyleSheets($this->tid);

        foreach($stylesheets as $stylesheet) {
            copy("{$this->css_path}/{$this->theme}/{$stylesheet['name']}", "cache/themes/theme{$stylesheet['tid']}/{$stylesheet['name']}");
        }

        return $result ? $this->connection->affected_rows : false;
    }

}