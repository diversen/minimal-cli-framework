#!/usr/bin/env php
<?php

include_once "vendor/autoload.php";
include_once "minimalCli.php";

use diversen\minimalCli;

class echoTest extends minimalCli {

    // Return main commands help
    public function getHelp() {
        return 
            array (
                'usage' => 'Command to make string to upper and lower case',
                'options' => array (
                    '--strtoupper' => 'Will put string in uppercase',
                    '--strtolower' => 'Will put string in lowercase'),
                
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
$m = new minimalCli();
$m->commands = $commands;
$m->header = $header;
$m->runMain();
