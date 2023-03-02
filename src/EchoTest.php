<?php

namespace Diversen;

class EchoTest
{

    /**
     *  Get command definition
     * 'usage', 'options', 'main_options', 'arguments'
     */
    public function getCommand()
    {

        return [

            // Usage of the command
            'usage' => "Command reads a file and output it in upper or lower case",

            // Options for only this command
            'options' => [
                '--up' => 'Will put string in uppercase',
                '--low' => 'Will put string in lowercase'
            ],

            // 'cast' => [
            //     'up' => 'int', // Cast to int, bool, float. Default is string
            // ],

            // Main options, which other commands may have access to
            'main_options' => [
                '--main' => 'Test with a main option'
            ],

            // Are there any arguments and what are they used for.
            // This is only for displaying help. Any number of arguments can be
            'arguments' => [
                'File' => 'Read from a file and out put to stdout',
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
        } else if ($args->getOption('low')) {
            $output = strtolower($input) . PHP_EOL;
        } else {
            $output = $input . PHP_EOL;
        }

        echo $output;
        return 0;
    }
}
