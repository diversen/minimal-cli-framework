<?php

namespace Diversen;

use Diversen\Padding;
use Diversen\ParseArgv;
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
     * @var \Diversen\parseArgv
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
        $main_options = [
            'main_options' => [
                '--help' => 'Will output help. Specify command followed by --help to get specific help on a command',
                '-h'     => 'Shorthand for --help',
                '--verbose' => 'verbose output'
            ],
        ];

        // Get all commands main options
        $help_ary = $this->getAllCommandsHelp();
        foreach ($help_ary as $val) {
            if (isset($val['main_options'])) {
                $main_options['main_options'] = array_merge(
                    $main_options['main_options'],
                    $val['main_options']
                );
            }
        }

        return $main_options;
    }

    /**
     * Run the main script
     */
    public function runMain()
    {
        $this->parse = new ParseArgv();
        $command = $this->parse->getArgument(0);

        if (isset($this->commands[$command])) {

            // Unset command from arguments
            $this->parse->unsetArgument(0);
            $res = $this->executeCommand($command);
            exit($res);
        }

        $this->executeMainHelp();
        exit(1);
    }

    /**
     * Get help section of all commands as an array
     */
    private function getAllCommandsHelp()
    {

        $help = [];
        foreach ($this->commands as $command_name => $command_obj) {
            $help[$command_name] = $command_obj->getCommand();
        }
        return $help;
    }

    /**
     * Display the main help
     */
    private function executeMainHelp()
    {

        global $argv;

        $str = $this->header . $this->NL . $this->NL;

        $p = new Padding();

        $help_main = $this->getHelpMain();

        // Usage
        $str .= $this->colorOutput('Usage', $this->colorNotice) . $this->NL;
        $str .= '  ' . $this->colorOutput($argv[0], $this->colorSuccess) . ' [--options] [command] [--options] [arguments]';
        $str .= $this->NL . $this->NL;

        // Main options
        $main_options = $help_main['main_options'];
        $ary_main = [];
        foreach ($main_options as $option_name => $description) {
            $ary_main[] = [$this->colorOutput($option_name, $this->colorSuccess), $description];
        }

        $str .= $this->colorOutput('Options across all commands', $this->colorNotice) . $this->NL;
        $str .= $p->padArray($ary_main) . $this->NL;

        // Show all commands
        $help_ary = $this->getAllCommandsHelp();

        $command_ary = [];
        foreach ($help_ary as $command_name => $command_help) {
            $command = [];
            $command[] = $this->colorOutput($command_name, $this->colorSuccess);
            $command[] = $command_help['usage'];
            $command_ary[] = $command;
        }

        $str .= $this->colorOutput("Available commands", $this->colorNotice) . $this->NL;
        $str .= $p->padArray($command_ary);
        echo $str;
    }

    /**
     * Color a string according to a color
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
     * Return an empty array if no command option or command arguments
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
    private function executeCommand($command)
    {

        $command_obj = $this->commands[$command];
        if (isset($this->parse->options['help']) || isset($this->parse->options['h'])) {
            $this->executeCommandHelp($command);
            exit(0);
        }

        if ($this->validateCommandOptions($command) !== true) {
            $invalid_option = $this->validateCommandOptions($command);
            echo $this->colorOutput($invalid_option . " is not allowed as option\n", $this->colorError);
            exit(128);
        }

        return $command_obj->runCommand($this->parse);
    }

    /**
     * Validate options given to a command
     * @param string the command
     * @return mixed true if the command is valid or a string with the invalid option
     */
    private function validateCommandOptions($command)
    {
        $allowed = $this->getAllowedOptions($command);
        $options = array_keys($this->parse->options);

        foreach ($options as $option) {
            if (!in_array($option, $allowed)) {
                return $option;
            }
        }
        return true;
    }

    /**
     * Get all allowed options from main and a single command
     * @param string command
     * @return array allowed options
     */
    private function getAllowedOptions($command)
    {

        // Allowed main options
        $main = $this->getHelpMain();
        $allowed_options = array_keys($main['main_options']);

        // Allow command options
        $command_help = $this->getAllCommandsHelp();
        if (isset($command_help[$command])) {
            $command_help[$command] = $this->validateHelp($command_help[$command]);
            $allowed_command_options = array_keys($command_help[$command]['options']);
            $allowed_options = array_merge($allowed_options, $allowed_command_options);
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
