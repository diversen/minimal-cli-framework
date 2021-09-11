<?php

namespace Diversen;

/**
 * Parse argv and get '-' and '--' flags and values, and get any value without a flag.
 */
class ParseArgv {
    
        
    
    /**
     * var holding $flags as key => value 
     * @var array 
     */
    public $flags = array ();
    
    /**
     * var holding any values without flags
     * @var array 
     */
    public $values = array ();
    
    /**
     * var holding any values without flags by key numbers
     * @var array 
     */
    public $valuesByKey = array ();
    
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
        
        // Set flagValues '--flag=test', '-f=test'
        // Set valuesBy
        foreach ($argv_ as $arg) {
            // - and -- are commands
            if (preg_match("/^[-]{1,2}/", $arg)) {
                $arg = preg_replace("/^[-]{1,2}/", '', $arg);
                
                $flag = $this->getFlagKey($arg);
                $value = $this->getFlagValue($arg);
                $this->flags[$flag] = $value;
            } else {
                $this->values[$arg] = $arg;
                $this->valuesByKey[] = $arg;
            }
        }
    }
    
    /**
     * Get flag key from arg
     * @param string $arg
     * @return string $value
     */
    private function getFlagKey ($arg) {
        $ary = explode('=', $arg);
        return $ary[0];
    }
    
    /**
     * Get flag value from arg
     * @param string $arg
     * @return string
     */
    private function getFlagValue ($arg) {
        $ary = explode('=', $arg);
        if (empty($ary[1])) {
            return '';
        }
        return $ary[1];
    }
    
    
    /**
     * Return a flag value by key
     */
    public function getFlag ($key) {
        if (isset($this->flags[$key])) {
            if ($this->flags[$key] === '') {
                return true;
            }
            return $this->flags[$key];
        }
    }
    
    /**
     * Return a value by key
     */
    public function getValue ($key) {
        if (isset($this->values[$key])) {
            return $this->values[$key];
        }
    }
    
    /**
     * Get a 
     */
    public function getValueByKey ($key) {
        if (isset($this->valuesByKey[$key])) {
            return $this->valuesByKey[$key];
        }
    }
    
    /**
     * Unset a value from valuesByKey. 
     * It is used when a command is found, so that the command does not count as argument
     */
    public function unsetValue(string $val) {
        foreach($this->valuesByKey as $k => $value) {
            
            if ($value === $val) {
                unset($this->valuesByKey[$k]);
                $this->valuesByKey = array_values($this->valuesByKey);
            }
        }
    }
}
