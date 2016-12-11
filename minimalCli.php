<?php

namespace diversen;

use JakubOnderka\PhpConsoleColor\ConsoleColor;
use diversen\parseArgv;
use diversen\padding;

class minimalCli {

    public $commands = [];
    public $parse = null;
    public $colorSuccess = 'green';
    public $colorNotice = 'yellow';
    public $colorError = 'red';
    public $header = 'Minmal-cli-framework';
    
    public function __constract() {
        
    }

    
    public function getHelpMain () {
        return array (
            'usage' => 'A test commandline program using minimal-cli-framework',
            'options' => array (
                    '--help' => 'Will output help',
                    '--verbose' => 'verbose output')
        );
    }
    
    /**
     * Run the CLI program
     */
    public function runMain() {
        $this->parse = new parseArgv();
        
        $executed = false;
               
        // Look for command
        foreach ($this->commands as $key => $command) {
            
            if ($this->parse->getValue($key)) {
                
                $this->parse->unsetValueByValue($key);
                $this->execute($key);
                $executed = true;
            }
        }
        
        if (!$executed) {
            $this->executeMainHelp();
        }
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
        
        $str = $this->header . PHP_EOL . PHP_EOL;
        
        $p = new padding();
        
        // command [options] [arguments]
        $help_main = $this->getHelpMain();
        
        $str.= $this->colorOutput('Usage', $this->colorNotice) . PHP_EOL;
        $str.= '  ' . $help_main['usage'] . PHP_EOL . PHP_EOL;
        
        $main_options = $help_main['options'];
        
        $ary = [];
        foreach($main_options as $option => $desc) {
            $ary[] = array (
                $this->colorOutput($option, $this->colorSuccess), $desc
            );
        }
        
        $str.= $this->colorOutput('Options', $this->colorNotice) . PHP_EOL;
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
     * @param type $command
     */
    public function execute($command) {
        $obj = $this->commands[$command];
        if (isset($this->parse->flags['help'])) {
            if (method_exists($obj, 'getHelp')) {
                $this->executeCommandHelp($command);
                exit(0);
            }
        }

        $obj->runCommand($this->parse);
    }
    
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

    public function outputMessage() {
        
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
