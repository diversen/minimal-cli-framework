#!/usr/bin/env php
<?php

include_once "vendor/autoload.php";

// You will not need the following include if you are autoloading 
// with the above include. This is just to make it easy to test.
include_once "minimalCli.php";

use diversen\MinimalCli;

class echoTest {

    // Return main commands help
    public function getCommand() {
        return 
            array (
                'usage' => 'Command to make string to upper and lower case',
                'options' => array (
                    '--strtoupper' => 'Will put string in uppercase',
                    '--strtolower' => 'Will put string in lowercase'),
                // Add a main options, which all commands will have access to
                'main_options' => array (
                    '--main' => 'Test with a main option'
                ),
                
                'arguments' => array (
                    'File' => 'Read from a file and out put to stdout'
                )
            );
    }

    /**
     * 
     * @param diversen\parseArgv $args
     */
    public function runCommand($args) {

        $str = '';
        $file = $args->getValueByKey(0);
        if ($file) {
            $str = $this->getFileStr($file);
        } else {
            echo "Specify file" . "\n";
            exit(0);
        }
        
        if ($args->getFlag('strtoupper')) {
            echo strtoupper($str) . "\n";
        }
        if ($args->getFlag('strtolower')) {
            echo strtolower($str) . PHP_EOL;
        }
        return 0;
    }
    
    public function getFileStr ($file) {
        if ($file && file_exists($file)) {
            return file_get_contents($file);  
        } 
    }
}

$header = <<<EOF
 _____         _   
|_   _|       | |  
  | | ___  ___| |_ 
  | |/ _ \/ __| __|
  | |  __/\__ \ |_ 
  \_/\___||___/\__|

EOF;

$echo = new echoTest();
$commands['echo'] = $echo;
$m = new MinimalCli();
$m->commands = $commands;
$m->header = $header;
$m->runMain();
