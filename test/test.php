#!/usr/bin/env php
<?php

include_once "vendor/autoload.php";

use Diversen\MinimalCli;

class echoTest
{

    // Return main commands help
    public function getCommand()
    {
        return [

            'usage' => 'Command to make string to upper and lower case',

            // Command options
            'options' => [
                '--up' => 'Will put string in uppercase',
                '--low' => 'Will put string in lowercase'
            ],

            // Add a main options, which all commands will have access to
            'main_options' => [
                '--main' => 'Test with a main option'
            ],

            'arguments' => [
                'File' => 'Read from a file and out put to stdout'
            ]
        ];
    }

    /**
     * Run the command and return the result 
     * @param Diversen\ParseArgv $args
     */
    public function runCommand(\Diversen\ParseArgv $args)
    {

        $file = $args->getArgument(0);
        if (!$file) {
            echo "No file was specified" . PHP_EOL;
            return 1;
        }

        if (!file_exists($file)) {
            echo "No such file" . PHP_EOL;
            return 1;
        }

        $input = file_get_contents($file);

        if ($args->getOption('up')) {
            $output = strtoupper($input) . PHP_EOL;
        }
        else if ($args->getOption('low')) {
            $output = strtolower($input) . PHP_EOL;
        } 
        else {
            $output = $input . PHP_EOL;
        } 

        echo $output;
        return 0;
    }
}

/**
 * Create a program where 'echo' is a command
 */

$header = <<<EOF
TEST ECHO COMMAND
EOF;

$echo = new echoTest();
$commands['echo'] = $echo;

/**
 * Default settings for the CLI program. Only colors of output can be used
 * These are the default settings
 */
$settings = [
    'colorError' => 'red',
    'colorSuccess' => 'green',
    'colorNotice' => 'yellow',
];

$m = new MinimalCli(['colorError' => 'bg_light_red']);
$m->commands = $commands;
$m->header = $header;
$m->runMain();
