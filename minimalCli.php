<?php

namespace diversen;

use JakubOnderka\PhpConsoleColor\ConsoleColor;
use diversen\parseArgv;
use diversen\padding;

class minimalCli {

    public $commands = [];
    
    /**
     *
     * @var \diversen\parseArgv
     */
    public $parse = null;
    public $colorSuccess = 'green';
    public $colorNotice = 'yellow';
    public $colorError = 'red';
    public $header = 'Minmal-cli-framework';
    
    public function __constract() {}

    /**
     * Main options that all commands has access to
     * @return type
     */
    public function getHelpMain () {
        
        // Built-in main options
        $main_options = array (
            'main_options' => array (
                    '--help' => 'Will output help. Specify command followed by --help to get specific help on a command',
                    '--verbose' => 'verbose output')
        );
        
        // Get all commands main options
        $help_ary = $this->getHelp();
        foreach($help_ary as $key => $val){
            if (isset($val['main_options'])){
                $main_options['main_options'] = array_merge(
                        $main_options['main_options'], $val['main_options']);
            }
        }
        return $main_options;
    }
    
    /**
     * Run the CLI program
     */
    public function runMain() {
        $this->parse = new parseArgv();
               
        // Look for command
        foreach ($this->commands as $key => $command) {
            
            if ($this->parse->getValue($key)) {             
                $this->parse->unsetValue($key);
                $res = $this->execute($key);
                exit($res);
            }
        }

        $this->executeMainHelp();

    }
    

    public function getHelp () {
        
        $help = [];
        foreach ($this->commands as $key => $command) {
            if (method_exists($command, 'getHelp')) {
                $help[$key] = $command->getHelp();
            }
        }
        return $help;
    }
    
    /**
     * Displays help text
     */
    public function executeMainHelp () {
        
	global $argv;
	$str = $this->header . PHP_EOL . PHP_EOL;

        $p = new padding();
        
        $help_main = $this->getHelpMain();
        
        // Usage
        $str.= $this->colorOutput('Usage', $this->colorNotice) . PHP_EOL;
	$str.= '  ' . $this->colorOutput($argv[0], $this->colorSuccess) . ' [--options] [command] [--options] [arguments]' . PHP_EOL . PHP_EOL;
        
        $main_options = $help_main['main_options'];
        $ary = [];
        foreach($main_options as $option => $desc) {
            $ary[] = array (
                $this->colorOutput($option, $this->colorSuccess), $desc
            );
        }
        
        $str.= $this->colorOutput('Options across all commands', $this->colorNotice) . PHP_EOL;
        $str.= $p->padArray($ary) . PHP_EOL;
        
        $help_ary = $this->getHelp();
        
        $ary = [];
        foreach($help_ary as $key => $val) {
            $a[] = $this->colorOutput($key, $this->colorSuccess);
            $a[] = $val['usage'];
            $ary[] = $a;
        }
        
        $str.=  $this->colorOutput("Available commands", $this->colorNotice) . PHP_EOL;
        $str.= $p->padArray($ary);
        echo $str;
    }
    
    /**
     * Color a string according
     * @param string $str
     * @param string $color
     * @return string $str colored
     */
    public function colorOutput($str, $color = '') {
        $consoleColor = new ConsoleColor();
        return $consoleColor->apply("$color", $str);
    }
   
    /**
     * Get max length of help in order to know
     * how wide to pad a string
     * @param array $helps
     * @return int $width
     */
    public function getStrMaxLength ($helps, $color = false) {
        
        $max_length = 0;
        foreach($helps as $key => $help) {
            $length = $this->getStrLength($key, $color);
            if ($length > $max_length){
                $max_length = $length;
            }
        }
        return $max_length;
    }
    
    /**
     * Get string length
     * @param string $str
     * @param string $color
     * @return int $length
     */
    public function getStrLength ($str, $color = false) {
        if ($color) {
            $str = $this->colorOutput($str, $color);
        }
        $length = strlen($str);
        return $length;
    }

    /**
     * Execute a command
     * @param string $command
     */
    public function execute($command) {

        $obj = $this->commands[$command];
        if (isset($this->parse->flags['help'])) {
            if (method_exists($obj, 'getHelp')) {
                $this->executeCommandHelp($command);
                exit(0);
            }
        }
        $res = $this->validateCommand($command);
        if ($res !== true) {
            echo $this->colorOutput($res . " is not allowed as option\n", $this->colorError);
            exit(128);
        }
        return $obj->runCommand($this->parse);
    }
    
    /**
     * Validate a command
     * @param string $command
     * @return mixed true if command is OK else the command as str
     */
    public function validateCommand($command) {
        $allowed = $this->getAllowedOptions($command);
        foreach($this->parse->flags as $key => $flag) {
            if (!in_array($key, $allowed)) {
                return $key;
            }
        }
        return true;
    }
    
    /**
     * Get all allowed options from main and command
     * @param string $command
     * @return array $allowed
     */
    public function getAllowedOptions ($command){
        
        // Allowed main options
        $main = $this->getHelpMain();
        $allowed_options = array_keys($main['main_options']);

        // Allow command options
        $command_help = $this->getHelp();
        if (isset($command_help[$command])) {
            $allowed_options = array_merge(
                    $allowed_options, 
                    array_keys($command_help[$command]['options']));
        }
        
        $allowed = [];
        // Clean options from -- and -
        foreach($allowed_options as $option) {
            $allowed[] = preg_replace("/^[-]{1,2}/", '', $option);
        }
        return $allowed;
    }
    
    /**
     * Execute specified command help
     * @param string $command
     */
    public function executeCommandHelp($command) {
        $obj = $this->commands[$command];
        $help = $obj->getHelp();
        
        $output =  $this->colorOutput("Usage", $this->colorNotice) . PHP_EOL;
        $output.= '  ' . $help['usage'] . PHP_EOL . PHP_EOL;

        $p = new padding();
        $options = $help['options'];
        
        $ary = [];
        foreach($options as $option => $desc) {
            $ary[] = array (
                $this->colorOutput($option, $this->colorSuccess), $desc
            );
        }
         
        $output.=  $this->colorOutput("Options:", $this->colorNotice) . PHP_EOL;
        $output.= $p->padArray($ary);
        
        $arguments = $help['arguments'];
        
        $ary = [];
        foreach($arguments as $argument => $desc) {
            $ary[] = array (
                $this->colorOutput($argument, $this->colorSuccess), $desc
            );
        }
        $output.=  PHP_EOL;
        $output.=  $this->colorOutput("Arguments:", $this->colorNotice) . PHP_EOL;
        $output.= $p->padArray($ary);
        
        echo $output;

    }
    
    /**
     * Get terminal width
     */
    public function getTerminalWidth() {
        return 80;
    }

    /**
     * 
     * @param type $text
     * @param type $lw
     * @return type
     */
    public function wrap($text) {

        $lw = $this->getTerminalWidth();
        return wordwrap($text, $lw, "\n", false);
    }
}
