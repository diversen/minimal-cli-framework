<?php

namespace diversen;

use diversen\parseArgv;

class minimalCli {

    public $commands = [];
    public $parse = null;

    /**
     * Run the CLI program
     */
    public function run() {
        $this->parse = new parseArgv();
        $executed = false;
        // Look for first sub command
        foreach ($this->commands as $key => $command) {
            
            if (isset($this->parse->values[$key])) {
                $this->execute($key);
                $executed = true;
            }
        }
        
        if (!$executed) {
            $this->displayHelp();
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
    public function displayHelp () {
        $helps = $this->getHelp();
        echo $length = $this->getMaxHelpLength($helps) +1;
        
        $str =  "Available commands" . PHP_EOL;
        foreach ($helps as $key => $help) {
            
            $str.= str_pad($key, $length , ' '); 
            $str.= $help['main'] . PHP_EOL;
        }
        echo $str;
    }
    
    public function getMaxHelpLength ($helps) {
        
        $max_length = 0;
        foreach($helps as $key => $help) {
            $length = strlen($key);
            if ($length > $max_length){
                $max_length = $length;
            }
        }
        return $max_length;
    }

    /**
     * Execute a command
     * @param type $command
     */
    public function execute($command) {
        $obj = $this->commands[$command];
        if (isset($this->parse->flags['help'])) {
            if (method_exists($obj, 'help')) {
                echo $obj->help();
            }
        }

        $obj->run($this->parse);
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
