<?php

namespace diversen;

use diversen\padding;
use diversen\ParseArgv;
use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class MinimalCli
{

    /**
     * Array holding command objects
     * @var array $commands
     */
    public $commands = [];

    /**
     *
     * @var \diversen\parseArgv
     */
    public $parse = null;

    /**
     * Color of success
     */
    public $colorSuccess = 'green';

    /**
     * Color of notice
     */
    public $colorNotice = 'yellow';

    /**
     * Color of error
     */
    public $colorError = 'red';

    /**
     * Set a header notice
     */
    public $header = 'Minimal-cli-framework';

    /**
     * Newline definition
     */
    private $NL = "\n";

    /**
     * Get main options that all commands has access to.
     * @return array $main_options
     */
    private function getHelpMain()
    {

        // Built-in main options
        $main_options = array(
            'main_options' => array(
                '--help' => 'Will output help. Specify command followed by --help to get specific help on a command',
                '--verbose' => 'verbose output'),
        );

        // Get all commands main options
        $help_ary = $this->getHelp();
        foreach ($help_ary as $val) {
            if (isset($val['main_options'])) {
                $main_options['main_options'] = array_merge(
                    $main_options['main_options'], $val['main_options']);
            }
        }

        return $main_options;
    }

    /**
     * Run the main CLI script
     */
    public function runMain()
    {
        $this->parse = new ParseArgv();
        $keys = array_keys($this->commands);

        foreach ($keys as $command) {

            if ($this->parse->getValue($command)) {
                $this->parse->unsetValue($command);
                $res = $this->execute($command);
                exit($res);
            }
        }

        $this->executeMainHelp();
    }

    /**
     * Get help from a all commands
     */
    private function getHelp()
    {

        $help = [];
        foreach ($this->commands as $key => $command) {
            if (method_exists($command, 'getCommand')) {
                $help[$key] = $command->getCommand();
            }
        }
        return $help;
    }

    /**
     * Displays help text
     */
    private function executeMainHelp()
    {

        global $argv;
        
        $str = $this->header . $this->NL . $this->NL;

        $p = new padding();

        $help_main = $this->getHelpMain();

        // Usage
        $str .= $this->colorOutput('Usage', $this->colorNotice) . $this->NL;
        $str .= '  ' . $this->colorOutput($argv[0], $this->colorSuccess) . ' [--options] [command] [--options] [arguments]';
        $str .= $this->NL . $this->NL;

        $main_options = $help_main['main_options'];
        $ary_main = [];
        foreach ($main_options as $option => $desc) {
            $ary_main[] = array(
                $this->colorOutput($option, $this->colorSuccess), $desc,
            );
        }

        $str .= $this->colorOutput('Options across all commands', $this->colorNotice) . $this->NL;
        $str .= $p->padArray($ary_main) . $this->NL;

        $help_ary = $this->getHelp();

        $ary_sub = [];
        foreach ($help_ary as $key => $val) {
            $a = [];
            $a[] = $this->colorOutput($key, $this->colorSuccess);
            $a[] = $val['usage'];
            $ary_sub[] = $a;
        }

        $str .= $this->colorOutput("Available commands", $this->colorNotice) . $this->NL;
        $str .= $p->padArray($ary_sub);
        echo $str;
    }

    /**
     * Color a string according
     * @param string $str
     * @param string $color
     * @return string $str colored
     */
    public function colorOutput($str, $color = 'y')
    {

        if ($color == 'y') {
            $color = $this->colorNotice;
        }

        if ($color == 'g') {
            $color = $this->colorSuccess;
        }

        if ($color == 'r') {
            $color = $this->colorError;
        }

        $consoleColor = new ConsoleColor();
        if ($consoleColor->isSupported()) {
            return $consoleColor->apply("$color", $str);
        }
        return $str;

    }



    /**
     * Method to validate command and fill in empty array
     * @param array $command
     */
    private function validateHelp($command)
    {
        if (!isset($command['options'])) {
            $command['options'] = [];
        }
        if (!isset($command['arguments'])) {
            $command['arguments'] = [];
        }
        return $command;
    }

    /**
     * Execute a command
     * @param string $command
     */
    public function execute($command)
    {

        $obj = $this->commands[$command];

        $allowed_options = $this->getAllowedOptions($command);
        $this->prepareFlags($allowed_options);

        if (isset($this->parse->flags['help'])) {
            if (method_exists($obj, 'getCommand')) {
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

    private function prepareFlags($allowed_options)
    {

        foreach ($this->parse->flags as $flag => $key) {
            $this->checkShorthand($allowed_options, $flag);

        }
    }

    private function checkShorthand($allowed_options, $flag)
    {

        $c = 0;
        $set_option = '';
        $possible = [];
        foreach ($allowed_options as $key => $option) {

            if (empty(trim($flag))) {
                continue;
            }

            // Fine exact match
            if ($option == $flag) {
                return;
            }

            // Match between option and a flag
            if (strpos($option, $flag) === 0) {
                $possible[] = $option;
                $set_option = $option;
                $c++;
            }
        }

        if ($c === 1) {
            $value = $this->parse->getFlag($flag);
            $this->parse->flags[$set_option] = $value;
            unset($this->parse->flags[$flag]);
            return true;
        } else if ($c > 1) {
            $str = "Ambiguous shorthand for option given: ";
            $str .= $this->colorOutput($flag, $this->colorError) . $this->NL;
            $str .= "Possible values are: " . $this->colorOutput(implode(', ', $possible), $this->colorNotice) . $this->NL;
            echo $str;
            exit(128);
        }
    }



    /**
     * Validate a command
     * @param string $command
     * @return mixed true if command is OK else the command as str
     */
    private function validateCommand($command)
    {
        $allowed = $this->getAllowedOptions($command);

        foreach ($this->parse->flags as $key => $flag) {
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
    public function getAllowedOptions($command)
    {

        // Allowed main options
        $main = $this->getHelpMain();
        $allowed_options = array_keys($main['main_options']);

        // Allow command options
        $command_help = $this->getHelp();
        if (isset($command_help[$command])) {

            $command_help[$command] = $this->validateHelp($command_help[$command]);
            $allowed_options = array_merge(
                $allowed_options, array_keys($command_help[$command]['options']));
        }

        $allowed = [];

        // Clean options from -- and -
        foreach ($allowed_options as $option) {
            $allowed[] = preg_replace("/^[-]{1,2}/", '', $option);
        }
        return $allowed;
    }

    /**
     * Execute specified command help
     * @param string $command
     */
    public function executeCommandHelp($command)
    {
        $obj = $this->commands[$command];
        $help = $obj->getCommand();
        $help = $this->validateHelp($help);

        // Usage should always be set
        $output = $this->colorOutput("Usage", $this->colorNotice) . $this->NL;
        $output .= '  ' . $help['usage'] . $this->NL;

        $p = new padding();

        $options = $help['options'];

        // Fill array with options and descriptions
        $ary = [];
        if (!empty($options)) {
            $output .= $this->NL;
            foreach ($options as $option => $desc) {
                $ary[] = array(
                    $this->colorOutput($option, $this->colorSuccess), $desc,
                );
            }

            $output .= $this->colorOutput("Options:", $this->colorNotice) . $this->NL;
            $output .= $p->padArray($ary);
        }

        // Fill array with arguments and descriptions
        $arguments = $help['arguments'];
        if (!empty($arguments)) {
            $ary = [];
            foreach ($arguments as $argument => $desc) {
                $ary[] = array(
                    $this->colorOutput($argument, $this->colorSuccess), $desc,
                );
            }
            $output .= $this->NL;
            $output .= $this->colorOutput("Arguments:", $this->colorNotice) . $this->NL;
            $output .= $p->padArray($ary);
        }
        echo $output;
    }
}
