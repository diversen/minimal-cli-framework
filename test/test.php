#!/usr/bin/env php
<?php

include_once "vendor/autoload.php";

// You will not need the following include if you are autoloading 
// with the above include. This is just to make it easy to test.
include_once "MinimalCli.php";

use Diversen\MinimalCli;

class echoTest {

    // Return main commands help
    public function getCommand() {
        return 
            array (
                'usage' => 'Command to make string to upper and lower case',
                'options' => array (
                    '--strtoupper' => 'Will put string in uppercase',
                    '-u' => 'Shorthand for strtoupper',
                    '--strtolower' => 'Will put string in lowercase'),
                    '-l' => 'Shorthand for strtolower',
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
     * Run the command and return the result 
     * @param diversen\parseArgv $args
     */
    public function runCommand($args) {

        $file = $args->getArgument(0);
        if (!file_exists($file)) {
            echo "No such file" . PHP_EOL;
            return 1;
        }

        $input = file_get_contents($file);         
        if ($args->inOptions(['strtoupper', 'u'])) {
            $output = strtoupper($input) . PHP_EOL;
        }
        if ($args->inOptions(['strtolower', 'l'])) {
            $output = strtolower($input) . PHP_EOL;
        }

        echo $output;
        return 0;
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
