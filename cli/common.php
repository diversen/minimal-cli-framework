<?php

namespace diversen\cli;

use diversen\minimalCli;

/**
 * common helper function in CLI env.
 */
class common {

    /**
     * checks is a user is root
     * @return boolean $res true if yes else no
     */
    public static function isRoot() {
        if (!function_exists('posix_getuid')){
            return true;
        }
        if (0 == posix_getuid()) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * examine if user is root. If not exit, and echo a message
     * @param string $str
     * @return int 0 on success else a positive int. 
     */
    public static function needRoot($str = '') {

        $output = '';
        $output.= "Current command needs to be run as root. E.g. with sudo: ";
        if (!empty($str)) {
            $output.="\nsudo $str";
        }

        if (!self::isRoot()) {
            echo $output . PHP_EOL;
            exit(128);
        }
        return 0;
    }
    
    /**
     * checks if we are in cli env
     * @return boolean $res true if we are and false
     */
    public static function isCli () {
        if (isset($_SERVER['SERVER_NAME'])){
            return false;
        }
        return true;
    }

    /**
     * echos a colored status message
     * @param string $status (e.g. UPGRADE, NOTICE, ERROR)
     * @param char $color the color to print, e.g. 'y', 'r', 'g'
     * @param string $mes the long status message to be appended to e.g. 
     *               'module upgrade failed'
     * @return void
     */
    public static function echoStatus($status, $color, $mes) {
        
        if ($color == 'y') {
            $color = minimalCli::$colorNotice;
        }
        
        if ($color == 'g') {
            $color = minimalCli::$colorSuccess;
        }
        
        if ($color == 'r') {
            $color = minimalCli::$colorError;
        }
        
        if (self::isCli()) {
            echo self::colorOutput(self::getColorStatus("[$status]"), $color);
            echo $mes . "\n";
        } else {
            self::echoMessage($status);
        }
        return;
    }

    /**
     * calculate and gets correct length of a status message,
     * e.g. [OK] and [NOTICE]
     * @param string $status [OK]
     * @return string $status
     */
    private static function getColorStatus($status) {
        $len = strlen($status);
        $add_spaces = 12 - $len;
        $status.=str_repeat(' ', $add_spaces);
        return $status;
    }

    /**
     * method for coloring output to command line
     * @param string $output
     * @param char $color_code (e.g. 'g', 'y', 'r')
     * @return string $colorered output
     */
    public static function colorOutput($output, $color = 'g') {

        if (!self::isCli()) {
            return $output;
        }
        
        return minimalCli::colorOutput($output, $color);
    }

    /**
     * simple function for printing a message
     * @param  string $mes the message to echo
     * @param  string $color Add a color to the output
     */
    public static function echoMessage($mes, $color = null) {
        if ($color) {
            echo self::colorOutput($mes . PHP_EOL, $color);
            return;
        }
        if (self::isCli()) {
            echo $mes . PHP_EOL;
        } else {
            echo $mes . "<br />\n";
        }
        return;
    }

    /**
     * Function for executing commands with php built-in command system
     * @param string $command to execute
     * @return int   $ret the value returned by the shell script being
     *                 executed through exec()
     */
    public static function systemCommand($command) {
        system($command . ' 2>&1', $ret);
        if ($ret == 0) {
            echo self::colorOutput(self::getColorStatus('[OK]'), minimalCli::$colorSuccess);
            echo $command . PHP_EOL;
        } else {
            echo self::colorOutput(self::getColorStatus('[ERROR]'), minimalCli::$colorError);
            echo $command . PHP_EOL;
        }
        return $ret;
    }
    
    /**
     * function for executing commands with php command exec
     * @param   string  $command to execute
     * @param   array   $options defaults to:
     *                  array ('silence' => false);
     * @return  mixed   $ret the value returned by the shell script being
     *                  executed through exec()
     */
    public static function execCommand($command, $options = array(), $echo_output = 1) {
        $output = array();
        exec($command . ' 2>&1', $output, $ret);
        if ($ret == 0) {
            if (!isset($options['silence'])) {
                echo self::colorOutput(self::getColorStatus('[OK]'), minimalCli::$colorSuccess);
                echo $command . PHP_EOL;
                if ($echo_output) {
                    echo self::parseShellArray($output);
                }
            }
        } else {
            if (!isset($options['silence'])) {
                echo self::colorOutput(self::getColorStatus('[ERROR]'), minimalCli::$colorError);
                echo $command . PHP_EOL;
                if ($echo_output) {
                    echo self::parseShellArray($output);
                }
            }
        }
        return $ret;
    }

    /**
     * transform an array of output from exec into a single string
     * @param array $output
     * @return string $str
     */
    public static function parseShellArray($output) {
        if (!is_array($output)) {
            return '';
        }
        $end_output = '';
        foreach ($output as $val) {
            $end_output.= $val . PHP_EOL;
        }
        return $end_output;
    }
    
    /**
     * a function for getting a confirm from command prompt
     * @param string $line a line to inform user what is going to happen
     * @param mixed $silence. If set we answer 'y' to all confirm readlines
     * @return int 1 on 'y' or 'Y' and 0 on anything else.
     */
    public static function readlineConfirm($line = null, $set_silence = null) {
        static $silence = null;
        if (isset($set_silence)) {
            $silence = 1;
        }
        if ($silence == 1) {
            return 1;
        }
        $str = $line;
        $str.= " Sure you want to continue? [Y/n]";
        $res = self::readSingleline($str);
        if (strtolower($res) == 'y') {
            return 1;
        } else {
            return 0;
        }
    }
    
    /**
     * command for aborting a script and printing info about abort
     * @param   string  $str string to be printed on abort
     * @return  int     $res  16
     */
    public static function abort($str = null) {
        if (isset($str)) {
            $str = $str . "\nAborting!";
        } else {
            $str = "Aborting!";
        }
        self::echoMessage(self::colorOutput($str, minimalCli::$colorError));
        exit(16);
    }
    
    /**
     * Read a single line from stdin
     *
     * @param string $str the str to print to screen
     * @return string $out the input which readline reads
     */
    public static function readSingleline($str) {
        echo $str;
        $out = "";
        $key = "";
        $key = fgetc(STDIN);      // read from standard input (keyboard)
        while ($key != "\n") {      // if the newline character has not yet arrived read another
            $out.= $key;
            $key = fread(STDIN, 1);
        }
        return $out;
    }
}
