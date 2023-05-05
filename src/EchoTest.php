<?php

namespace Diversen;

class EchoTest
{
    private $utils;
    public function __construct()
    {
        $this->utils = new \Diversen\Cli\Utils();
    }

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
                'File' => 'Read from a file and output to stdout. You can also pipe input to the command',
            ],
 
            // Set a default command if none if given
            // php demos/example --up README.md
            // Instead of:
            // php demos/example echo --up README.md
            // Then set 'is_default' to true
            // 'is_default' => true, 
        ];
    }

    private function getFileContents($file)
    {
        if (!file_exists($file)) {
            return false;
        }
        if (!file_exists($file)) {
            return false;
        }

        $input = file_get_contents($file);
        return $input;
    }

    /**
     * Run the command and return the result 
     * @param Diversen\ParseArgv $args
     */
    public function runCommand(\Diversen\ParseArgv $args)
    {

        $input = $this->utils->readStdin();
        if (empty($input)) {
            $file = $args->getArgument(0);
            $input = $this->getFileContents($file);
            if (!$input) {
                echo "No content was piped to STDIN or file was not specified" . PHP_EOL;
                return 12;
            }
        }

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
