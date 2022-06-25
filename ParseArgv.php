<?php

namespace Diversen;

/**
 * Parse argv and return an array with the options and arguments.
 */
class ParseArgv {
    
    /**
     * var holding $options as key => value 
     * @var array 
     */
    public $options = array ();
    
    /**
     * var holding any arguments without options
     * @var array 
     */
    public $arguments = array ();
    
    /**
     * Construct and parse global argv
     */
    public function __construct() {
        $this->parse();
    }
    
    /**
     * Parse argv
     * @global array $argv
     */
    public function parse() {
        global $argv;

        $argv_ = $argv;
        
        // Don't care about the php file
        unset($argv_[0]);

        foreach ($argv_ as $arg) {

            // Get commands ('-', '--')
            if (preg_match("/^[-]{1,2}/", $arg)) {
                $arg = preg_replace("/^[-]{1,2}/", '', $arg);
                $option = $this->getOptionKey($arg);
                $value = $this->getOptionsValue($arg);
                $this->options[$option] = $value;
            } 
            
            // Get arguments
            else {
                $this->arguments[] = $arg;
            }
        }
    }
    
    /**
     * Get option key from option string
     * @param string $arg
     * @return string $value
     */
    private function getOptionKey ($arg) {
        $ary = explode('=', $arg);
        return $ary[0];
    }
    
    /**
     * Get option value from option string
     * @param string $arg
     * @return string
     */
    private function getOptionsValue ($arg) {
        $ary = explode('=', $arg);
        if (empty($ary[1])) {
            return '';
        }
        return $ary[1];
    }
    
    
    /**
     * Return a option from options. If the option is not set return 'null'
     * If the option is set return the option value as a string. If the option is set
     * but does not have any value return true
     */
    public function getOption ($key) {
        if (isset($this->options[$key])) {

            // Flag exists, but no value
            if ($this->options[$key] === '') {
                return true;
            }

            // Flag has a value
            return $this->options[$key];
        }
    }
    
    /**
     * Check if a argument exists
     */
    public function argumentExists ($value) {
        foreach ($this->arguments as $val) {
            if ($val === $value) {
                return true;
            }
        }
    }
    
    /**
     * Get argument
     */
    public function getArgument ($key) {
        if (isset($this->arguments[$key])) {
            return $this->arguments[$key];
        }
    }
    
    /**
     * Unset a value from arguments_by_key. 
     * It is used when a sub-command is found, so that the sub-command does not count as an argument
     */
    public function unsetArgument(string $val) {
        foreach($this->arguments as $k => $value) {
            
            if ($value === $val) {
                unset($this->arguments[$k]);
            }
        }

        $this->arguments = array_values($this->arguments);
    }
}
