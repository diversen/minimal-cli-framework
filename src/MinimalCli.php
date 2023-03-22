<?php

namespace Diversen;

use Diversen\Padding;
use Diversen\ParseArgv;
use Diversen\Cli\Utils;
use Exception;

class MinimalCli
{

    protected ?\Diversen\parseArgv $parse_argv;
    protected ?\Diversen\CLI\Utils $utils;
    protected ?\Diversen\Padding $padding;
    private bool $test_mode = false;

    public array $commands = [];
    public string $header = 'Command Line Tool';
    public string $NL = PHP_EOL;

    private string $default_usage = "No usage defined to this command.";

    public function __construct(array $settings = [])
    {
        $this->utils = new Utils($settings);
        $this->parse_argv = new ParseArgv();
        $this->padding = new Padding();
    }

    /**
     * Test mode will return exit codes instead of exiting with exit()
     * @param array $argv
     */
    public function setTestMode(array $argv = [])
    {
        $this->test_mode = true;
        if ($argv) {
            $this->parse_argv = new ParseArgv($argv);
        }
    }

    public function exit($code = 0)
    {
        if ($this->test_mode) {
            return $code;
        }

        exit($code);
    }


    public function addCommandClass(string $name, string $class)
    {
        $this->validateCommand($class);
        $obj = new $class();
        $this->commands[$name] = $obj;
    }

    public function setHeader(string $header)
    {
        $this->header = $header;
    }

    public function runMain()
    {

        $command = $this->parse_argv->getArgument(0);
        $command = $this->getCommandShortcut($command);
        $command_exists = $this->commands[$command] ?? false;
        if ($command_exists) {
            // Unset command from arguments and execute
            $this->parse_argv->unsetArgument(0);
            $res = $this->executeCommand($command);
            return $this->exit($res);
        }

        $this->executeMainHelp();
        return $this->exit(1);
    }

    /**
     * Get main options that all commands has access to.
     * @return array $main_options
     */
    private function getHelpMain(): array
    {

        // Built-in main options
        $main_options = [
            'main_options' => [
                '--help' => 'Will output help. Specify command followed by --help to get specific help on a command',
                '--verbose' => 'verbose output'
            ],
        ];

        // Get all commands main options
        $definitions = $this->getAllCommandDefinitions();
        foreach ($definitions as $val) {
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
        if (!method_exists($class_or_obj, 'getCommand')) {
            throw new Exception('A command needs a `runCommand` and a `getCommand` method');
        }
    }

    /**
     * Check if the command name is a valid shortcut. 
     * E.g. 't' for 'translate'.
     */
    private function getCommandShortcut(?string $command_name)
    {

        if (!$command_name) return;
        $command_names = array_keys($this->commands);

        $set_command = '';
        $possible = [];

        foreach ($command_names as $command) {

            // Check if command_name can be used as shortcut for command
            if (strpos($command, $command_name) === 0) {
                $possible[] = $command_name;
                $set_command = $command;
            }
        }

        if (count($possible) === 1) {
            return $set_command;
        }

        if (count($possible) > 1) {

            $str = "Ambiguous shorthand for command given: ";
            $str .= $this->utils->colorOutput($command_name, 'error') . PHP_EOL;
            $str .= "Possible values are: " . $this->utils->colorOutput(implode(', ', $possible), 'notice') . PHP_EOL;
            echo $str;

            return $this->exit(128);
        }

        return false;
    }

    /**
     * This method is used if no `getCommand` method is defined in a command class
     * @return array $command
     */
    private function getDefaultCommand()
    {
        return [
            'usage' => $this->default_usage,
        ];
    }


    /**
     * Get all definitions of commands.
     */
    private function getAllCommandDefinitions(): array
    {

        $definitions = [];
        foreach ($this->commands as $command_name => $command_obj) {
            $definition = [];
            if (method_exists($command_obj, 'getCommand')) {
                $definition = $command_obj->getCommand();
                if (!isset($definition['usage'])) {
                    $definition['usage'] = $this->default_usage;
                }
            } else {
                $definition = $this->getDefaultCommand();
            }

            $definitions[$command_name] = $definition;
        }

        return $definitions;
    }

    /**
     * Display the main help
     */
    private function executeMainHelp(): void
    {

        global $argv;

        $str = $this->header . PHP_EOL . PHP_EOL;

        $help_main = $this->getHelpMain();

        // Usage
        $str .= $this->utils->colorOutput('Usage', 'notice') . PHP_EOL;

        $main_script = $argv[0];
        $main_command = basename($main_script);
        $str .= '  ' . $this->utils->colorOutput($main_command, 'success') . ' [--options] [command] [--options] [arguments]';
        $str .= PHP_EOL . PHP_EOL;

        // Main options
        $main_options = $help_main['main_options'];
        $ary_main = [];
        foreach ($main_options as $option_name => $description) {
            $ary_main[] = [$this->utils->colorOutput($option_name, 'success'), $description];
        }

        $str .= $this->utils->colorOutput('Options across all commands', 'notice') . PHP_EOL;
        $str .= $this->padding->padArray($ary_main) . PHP_EOL;

        // Show all commands
        $definitions = $this->getAllCommandDefinitions();

        $command_ary = [];
        foreach ($definitions as $command_name => $command_help) {
            $command = [];
            $command[] = $this->utils->colorOutput($command_name, 'success');
            $command[] = $command_help['usage'];
            $command_ary[] = $command;
        }

        $str .= $this->utils->colorOutput("Available commands", 'notice') . PHP_EOL;
        $str .= $this->padding->padArray($command_ary);
        echo $str;
    }


    /**
     * Return an empty array if no command option or command arguments
     * @param array $command
     */
    private function sanitizeCommandDefinition(array $command): array
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
            return $this->exit(0);
        }

        if ($this->validateCommandOptions($command) !== true) {
            $invalid_option = $this->validateCommandOptions($command);
            echo $this->utils->colorOutput($invalid_option . " is not allowed as option" . PHP_EOL, 'error');
            return $this->exit(128);
        }

        $command_definition = $command_obj->getCommand();

        $cast = $command_definition['cast'] ?? [];
        $this->parse_argv->castOptions($cast);

        return $command_obj->runCommand($this->parse_argv);
    }

    /**
     * Rewrite short options
     */
    private function rewriteShorthandOptions(array $allowed_options)
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
     * 
     * If it is NOT ambiguous, then set options['strtolower'] with the value of -s
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

            // Match between allowed option and option to check 
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
            $str .= $this->utils->colorOutput($option_to_check, 'error') . PHP_EOL;
            $str .= "Possible values are: " . $this->utils->colorOutput(implode(', ', $possible), 'notice') . PHP_EOL;
            echo $str;
            return $this->exit(128);
        }
    }

    /**
     * Validate options given to a command
     */
    private function validateCommandOptions(string $command)
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
     */
    private function getAllowedOptions(string $command): array
    {

        // Allowed main options
        $main = $this->getHelpMain();
        $allowed_options = array_keys($main['main_options']);

        // Allow command options
        $definitions = $this->getAllCommandDefinitions();
        if (isset($definitions[$command])) {
            $definitions[$command] = $this->sanitizeCommandDefinition($definitions[$command]);
            $allowed_command_options = array_keys($definitions[$command]['options']);
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
     * Execute a specified command help.
     */
    private function executeCommandHelp(string $command)
    {
        $command_definitions = $this->getAllCommandDefinitions();
        $definition = $this->sanitizeCommandDefinition($command_definitions[$command]);

        $output = $this->utils->colorOutput("Usage", 'notice') . PHP_EOL;
        $output .= '  ' . $definition['usage'] . PHP_EOL;

        $options = $definition['options'];

        // Fill array with options and descriptions
        $ary = [];
        if (!empty($options)) {
            $output .= PHP_EOL;
            foreach ($options as $option => $desc) {
                $ary[] = array(
                    $this->utils->colorOutput($option, 'success'), $desc,
                );
            }

            $output .= $this->utils->colorOutput("Options:", 'notice') . PHP_EOL;
            $output .= $this->padding->padArray($ary);
        }

        // Fill array with arguments and descriptions
        $arguments = $definition['arguments'];
        if (!empty($arguments)) {
            $ary = [];
            foreach ($arguments as $argument => $desc) {
                $ary[] = [$this->utils->colorOutput($argument, 'success'), $desc];
            }
            $output .= PHP_EOL;
            $output .= $this->utils->colorOutput("Arguments:", 'notice') . PHP_EOL;
            $output .= $this->padding->padArray($ary);
        }

        echo $output;
    }
}
