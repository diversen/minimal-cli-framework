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
    public function __construct(array $argv_ = null) {
        $this->parse($argv_);
    }
    
    /**
     * Parse argv. If $argv_ is not set then use global $argv
     * @global array $argv
     */
    public function parse(array $argv_ = null) {
        
        if (!$argv_) {
            global $argv;
            $argv_ = $argv;
        }
        
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
    public function getOption ($option) {
        if (isset($this->options[$option])) {

            // Flag exists, but no value
            if ($this->options[$option] === '') {
                return true;
            }

            // Flag has a value
            return $this->options[$option];
        }
    }

    /**
     * Checks if at least one option in a given array of options is set in the argv options
     */
    public function inOptions($options) {
        $argv_options = array_keys($this->options);
        foreach($argv_options as $option) {
            if (in_array($option, $options)) {
                return true;
            }
        }
        return false;
        
    }

    /**
     * Does an option exist, by value, e.g. 'help'
     */
    public function optionExists ($option) {
        return isset($this->options[$option]);
    }
    
    /**
     * Does an argument exists (by index, e.g. 0)
     */
    public function argumentExists ($key) {
        if (isset($this->arguments[$key])) {
            return true;
        }
    }
    
    /**
     * Get argument by index (e.g. 0)
     */
    public function getArgument ($key) {
        if (isset($this->arguments[$key])) {
            return $this->arguments[$key];
        }
    }
    
    /**
     * Unset a value from arguments_by_key. 
     */
    public function unsetArgument($key) {
        unset($this->arguments[$key]);

        $this->arguments = array_values($this->arguments);
    }
}
