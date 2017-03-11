<?php


class BaseCLI {
    protected $connection;
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

    public function checkConnection()
    {
        if ($this->connection->connect_error) {
            die('Connect Error (' . $this->connection->connect_errno . ') '. $this->connection->connect_error);
        }
    }

    /*
      Source:
      https://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
    */
    public function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $colored_string = "";

        if (isset($this->foregroundColors[$foregroundColor])) {
            $colored_string .= "\033[" . $this->foregroundColors[$foregroundColor] . "m";
        }
        if (isset($this->backgroundColors[$backgroundColor])) {
            $colored_string .= "\033[" . $this->backgroundColors[$backgroundColor] . "m";
        }

        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }

    public function setVersion($v)
    {
        $this->version = $v;
    }
}