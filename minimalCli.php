<?php

namespace diversen;

use JakubOnderka\PhpConsoleColor\ConsoleColor;
use diversen\parseArgv;
use diversen\padding;

class minimalCli {

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
     * @var string $colorSuccess
     */
    public static $colorSuccess = 'green';

    /**
     * Color of notice
     * @var string $colorNotice
     */
    public static $colorNotice = 'yellow';

    /**
     * Color of error
     * @var string $colorError
     */
    public static $colorError = 'red';

    /**
     * Set a header notice 
     * @var string $header
     */
    public $header = 'Minmal-cli-framework';
    
    private $NL = "\n";

    /**
     * Get main options that all commands has access to.
     * @return array $main_options
     */
    private function getHelpMain() {

        // Built-in main options
        $main_options = array(
            'main_options' => array(
                '--help' => 'Will output help. Specify command followed by --help to get specific help on a command',
                '--verbose' => 'verbose output')
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
    public function runMain() {
        $this->parse = new parseArgv();

        // Look for command
        foreach ($this->commands as $key => $command) {

            if ($this->parse->getValue($key)) {
                $this->parse->unsetValue($key);
                $res = $this->execute($key);
                exit($res);
            }
        }

        $this->executeMainHelp();
    }

    /**
     * Get help from a all commands
     * @return array $help
     */
    public function getHelp() {

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
    public function executeMainHelp() {

        global $argv;
        $str = $this->header . $this->NL . $this->NL;

        $p = new padding();

        $help_main = $this->getHelpMain();

        // Usage
        $str.= self::colorOutput('Usage', self::$colorNotice) . $this->NL;
        $str.= '  ' . self::colorOutput($argv[0], self::$colorSuccess) . ' [--options] [command] [--options] [arguments]';
        $str.= $this->NL . $this->NL;
        
        $main_options = $help_main['main_options'];
        $ary_main = [];
        foreach ($main_options as $option => $desc) {
            $ary_main[] = array(
                self::colorOutput($option, self::$colorSuccess), $desc
            );
        }

        $str .= self::colorOutput('Options across all commands', self::$colorNotice) . $this->NL;
        $str .= $p->padArray($ary_main) . $this->NL;

        $help_ary = $this->getHelp();

        $ary_sub = [];
        foreach ($help_ary as $key => $val) {
            $a = [];
            $a[] = self::colorOutput($key, self::$colorSuccess);
            $a[] = $val['usage'];
            $ary_sub[] = $a;
        }

        $str .= self::colorOutput("Available commands", self::$colorNotice) . $this->NL;
        $str .= $p->padArray($ary_sub);
        echo $str;
    }

    /**
     * Color a string according
     * @param string $str
     * @param string $color
     * @return string $str colored
     */
    public static function colorOutput($str, $color) {


        if ($color == 'y') {
            $color = self::$colorNotice;
        }

        if ($color == 'g') {
            $color = self::$colorSuccess;
        }

        if ($color == 'r') {
            $color = self::$colorError;
        }

        $consoleColor = new ConsoleColor();
        return $consoleColor->apply("$color", $str);
    }

    /**
     * Get max length of help in order to know
     * how wide to pad a string
     * @param array $helps
     * @return int $width
     */
    public function getStrMaxLength($helps, $color = false) {

        $max_length = 0;
        foreach ($helps as $key => $help) {
            $length = $this->getStrLength($key, $color);
            if ($length > $max_length) {
                $max_length = $length;
            }
        }
        return $max_length;
    }

    /**
     * Get string length
     * @param string $str
     * @param string $color
     * @return int $length
     */
    public function getStrLength($str, $color = false) {
        if ($color) {
            $str = self::colorOutput($str, $color);
        }
        $length = strlen($str);
        return $length;
    }

    /**
     * Method to validate command and fill in empty array
     * @param array $command
     */
    private function validateHelp($command) {
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
    public function execute($command) {

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
            echo self::colorOutput($res . " is not allowed as option\n", self::$colorError);
            exit(128);
        }

        return $obj->runCommand($this->parse);
    }

    /**
     * Checks if an option can be a short form of a defined option.
     * e.g. --save could be --save-file
     * If a command is ambiguous exit on display message
     * @param array $allow
     */
    private function prepareFlags($allowed_options) {
        
        foreach ($this->parse->flags as $flag => $key) {
            $this->checkShorthand($allowed_options, $flag);
                
            
        }
    }

    private function checkShorthand($allowed_options, $flag) {

        $c = 0;
        $set_option = '';
        $possible = [];
        foreach ($allowed_options as $key => $option) {
            
            // Fine exact match
            if ($option == $flag) {
                return;
            }
            
            // Match between option and a flag
            if ( strpos($option, $flag) === 0) {
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
            $str.= $this->colorOutput($flag, self::$colorError) . $this->NL;
            $str.= "Possible values are: " . $this->colorOutput(implode(', ', $possible), self::$colorNotice) . $this->NL;
            echo $str;
            exit(128);
        }
    }

    /**
     * Validate a command
     * @param string $command
     * @return mixed true if command is OK else the command as str
     */
    private function validateCommand($command) {
        $allowed = $this->getAllowedOptions($command);
        // $this->prepareFlags($allowed);

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
    public function getAllowedOptions($command) {

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
    public function executeCommandHelp($command) {
        $obj = $this->commands[$command];
        $help = $obj->getCommand();
        $help = $this->validateHelp($help);

        // Usage should always be set
        $output = $this->colorOutput("Usage", self::$colorNotice) . $this->NL;
        $output .= '  ' . $help['usage'] . $this->NL . $this->NL;

        $p = new padding();

        $options = $help['options'];

        // Fill array with options and descriptions
        $ary = [];
        if (!empty($options)) {
            foreach ($options as $option => $desc) {
                $ary[] = array(
                    $this->colorOutput($option, self::$colorSuccess), $desc
                );
            }
            $output .= $this->colorOutput("Options:", self::$colorNotice) . $this->NL;
            $output .= $p->padArray($ary);
        }


        // Fill array with arguments and descriptions
        $arguments = $help['arguments'];
        if (!empty($arguments)) {
            $ary = [];
            foreach ($arguments as $argument => $desc) {
                $ary[] = array(
                    $this->colorOutput($argument, self::$colorSuccess), $desc
                );
            }
            $output .= $this->NL;
            $output .= $this->colorOutput("Arguments:", self::$colorNotice) . $this->NL;
            $output .= $p->padArray($ary);
        }
        echo $output;
    }
}
