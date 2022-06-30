<?php

namespace Diversen;

use Diversen\Padding;
use Diversen\ParseArgv;
use Diversen\Cli\Utils;
use Exception;

class MinimalCli
{

    /**
     *
     * @var \Diversen\parseArgv
     */
    protected $parse_argv = null;

    /**
     * @var \Diversen\CLI\Utils
     */
    protected $utils;

    /**
     * @var \Diversen\Padding
     */
    protected $padding;

    /**
     * Array holding command objects
     */
    public $commands = [];

    /**
     * default program
     */
    public $header = 'Program 0.0.1';

    /**
     * Newline definition
     */
    protected $NL = PHP_EOL;

    public function __construct($settings = [])
    {
        $this->utils = new Utils($settings);
        $this->parse_argv = new ParseArgv();
        $this->padding = new Padding();
    }

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

    private function validateCommand($class_or_obj)
    {

        if (!method_exists($class_or_obj, 'runCommand') && !method_exists($class_or_obj, 'getCommand')) {
            throw new Exception('A command needs a `runCommand` and a `getCommand`');
        }
    }

    /**
     * Add a command to a program
     */
    public function addCommandObject(string $name, Object $command)
    {
        $this->validateCommand($command);
        $this->commands[$name] = $command;
    }

    /**
     * Add a class to a program
     */
    public function addCommandClass(string $name, string $class)
    {
        $this->validateCommand($class);
        $obj = new $class();
        $this->commands[$name] = $obj;
    }

    /**
     * Set the program header
     */
    public function setHeader(string $header)
    {
        $this->header = $header;
    }

    /**
     * Run the main script
     */
    public function runMain()
    {

        $command = $this->parse_argv->getArgument(0);

        if (!$command) {
            $this->executeMainHelp();
        } else if (isset($this->commands[$command])) {

            // Unset command from arguments
            $this->parse_argv->unsetArgument(0);
            $res = $this->executeCommand($command);
            exit($res);
        } else {
            echo $this->utils->colorOutput('No valid command', 'error') . $this->NL;
        }

        exit(1);
    }

    /**
     * Get help section of all commands as an array
     */
    private function getAllCommandsHelp(): array
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

        $help_main = $this->getHelpMain();

        // Usage
        $str .= $this->utils->colorOutput('Usage', 'notice') . $this->NL;
        $str .= '  ' . $this->utils->colorOutput($argv[0], 'success') . ' [--options] [command] [--options] [arguments]';
        $str .= $this->NL . $this->NL;

        // Main options
        $main_options = $help_main['main_options'];
        $ary_main = [];
        foreach ($main_options as $option_name => $description) {
            $ary_main[] = [$this->utils->colorOutput($option_name, 'success'), $description];
        }

        $str .= $this->utils->colorOutput('Options across all commands', 'notice') . $this->NL;
        $str .= $this->padding->padArray($ary_main) . $this->NL;

        // Show all commands
        $help_ary = $this->getAllCommandsHelp();

        $command_ary = [];
        foreach ($help_ary as $command_name => $command_help) {
            $command = [];
            $command[] = $this->utils->colorOutput($command_name, 'success');
            $command[] = $command_help['usage'];
            $command_ary[] = $command;
        }

        $str .= $this->utils->colorOutput("Available commands", 'notice') . $this->NL;
        $str .= $this->padding->padArray($command_ary);
        echo $str;
    }


    /**
     * Return an empty array if no command option or command arguments
     * @param array $command
     */
    private function validateHelp(array $command): array
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
     */
    private function executeCommand(string $command)
    {

        // Rewrite shorthand options
        // E.g. use -h instead or --help
        $allowed_options = $this->getAllowedOptions($command);
        $this->rewriteShorthandOptions($allowed_options);

        $command_obj = $this->commands[$command];
        if (isset($this->parse_argv->options['help'])) {
            $this->executeCommandHelp($command);
            exit(0);
        }

        if ($this->validateCommandOptions($command) !== true) {
            $invalid_option = $this->validateCommandOptions($command);
            echo $this->utils->colorOutput($invalid_option . " is not allowed as option" . $this->NL, 'error');
            exit(128);
        }

        return $command_obj->runCommand($this->parse_argv);
    }

    /**
     * Rewrite short options
     */
    private function rewriteShorthandOptions($allowed_options)
    {

        $options = array_keys($this->parse_argv->options);
        foreach ($options as $option) {
            $this->rewriteShorthand($allowed_options, $option);
        }
    }

    /** 
     * Check if an option can be used as shorthand. E.g: '--strtolower' may be an option
     * 
     * Check if e.g. '-s' is set then check if shorthand option is ambiguous
     * If it is not ambiguous, then set options['strtolower'] with the value 
     * */
    private function rewriteShorthand($allowed_options, $option_to_check)
    {

        $set_option = '';
        $possible = [];
        foreach ($allowed_options as $option) {

            // Found exact match. No ambiguous option
            if ($option == $option_to_check) {
                return;
            }

            // Match between allowed option adnd option to check 
            if (strpos($option, $option_to_check) === 0) {
                $possible[] = $option;
                $set_option = $option;
            }
        }

        $possible_options = count($possible);

        // Rewrite if only one possible valid option for the shorthand given
        if ($possible_options === 1) {
            $value = $this->parse_argv->getOption($option_to_check);
            $this->parse_argv->options[$set_option] = $value;
            unset($this->parse_argv->options[$option_to_check]);
            return true;
        }

        // If the shorthand option give has more than one valid option then exit with an error.
        if ($possible_options > 1) {
            $str = "Ambiguous shorthand for option given: ";
            $str .= $this->utils->colorOutput($option_to_check, 'error') . $this->NL;
            $str .= "Possible values are: " . $this->utils->colorOutput(implode(', ', $possible), 'notice') . $this->NL;
            echo $str;
            exit(128);
        }
    }

    /**
     * Validate options given to a command
     * @param string the command
     * @return mixed true if the command is valid or a string with the invalid option
     */
    private function validateCommandOptions($command)
    {
        $allowed = $this->getAllowedOptions($command);
        $options = array_keys($this->parse_argv->options);

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
    private function executeCommandHelp($command)
    {
        $obj = $this->commands[$command];
        $help = $obj->getCommand();
        $help = $this->validateHelp($help);

        // Usage should always be set
        $output = $this->utils->colorOutput("Usage", 'notice') . $this->NL;
        $output .= '  ' . $help['usage'] . $this->NL;

        $options = $help['options'];

        // Fill array with options and descriptions
        $ary = [];
        if (!empty($options)) {
            $output .= $this->NL;
            foreach ($options as $option => $desc) {
                $ary[] = array(
                    $this->utils->colorOutput($option, 'success'), $desc,
                );
            }

            $output .= $this->utils->colorOutput("Options:", 'notice') . $this->NL;
            $output .= $this->padding->padArray($ary);
        }

        // Fill array with arguments and descriptions
        $arguments = $help['arguments'];
        if (!empty($arguments)) {
            $ary = [];
            foreach ($arguments as $argument => $desc) {
                $ary[] = [$this->utils->colorOutput($argument, 'success'), $desc];
            }
            $output .= $this->NL;
            $output .= $this->utils->colorOutput("Arguments:", 'notice') . $this->NL;
            $output .= $this->padding->padArray($ary);
        }

        echo $output;
    }
}
