<?php

include_once "vendor/autoload.php";
include_once "minimalCli.php";

use diversen\minimalCli;

class echoTest {

    // Return main commands help
    public function getHelp() {
        return 
            array (
                'main' => 'Command to make string to upper and lower case',
                'options' => array (
                    '--struppr' => 'Will put string in uppercase',
                    '--lower' => 'Will put string in lowercase'),
                
                'arguments' => array (
                    'File' => 'File to use',
                    'Version' => 'Version'
                )
            );
    }

    /**
     * 
     * @param diversen\parseArgv $args
     */
    public function run($args) {
        if ($args->getFlag('strtoupper')) {
            echo strtoupper($args->getFlag('strtoupper')) . PHP_EOL;
        }
        if ($args->getFlag('strtolower')) {
            echo strtolower($args->getFlag('strtolower')) . PHP_EOL;
        }      
    }
}

$echo = new echoTest;
$commands['echo'] = $echo;
$m = new minimalCli;
$m->commands = $commands;
$m->run();
$m->getTerminalWidth();
