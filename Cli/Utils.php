<?php

namespace diversen\Cli;

use diversen\MinimalCli;

/**
 * common helper function in CLI env.
 */
class Utils extends MinimalCli
{

    /**
     * checks is a user is root
     * @return boolean $res true if yes else no
     */
    public function isRoot()
    {
        if (!function_exists('posix_getuid')) {
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
    public function needRoot(string $str = '')
    {

        $output = '';
        $output .= "Current command needs to be run as root. E.g. with sudo: ";
        if (!empty($str)) {
            $output .= "\nsudo $str";
        }

        if (!$this->isRoot()) {
            echo $output . PHP_EOL;
            exit(128);
        }
        return 0;
    }

    /**
     * checks if we are in cli env
     * @return boolean $res true if we are and false
     */
    public function isCli(): bool
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            return false;
        }
        return true;
    }

    /**
     * echo a colored status message
     */
    public function echoStatus(string $status, string $color, string $mes)
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

        if ($this->isCli()) {
            echo $this->colorOutput($this->getColorStatus("[$status]"), $color);
            echo $mes . "\n";
        } else {
            $this->echoMessage($status);
        }
        return;
    }

    /**
     * calculate and gets correct length of a status message,
     */
    private function getColorStatus(string $status)
    {
        $len = strlen($status);
        $add_spaces = 12 - $len;
        $status .= str_repeat(' ', $add_spaces);
        return $status;
    }

    /**
     * method for printing a message
     */
    public function echoMessage($mes, $color = null)
    {
        if ($color) {
            echo $this->colorOutput($mes . PHP_EOL, $color);
            return;
        }
        if ($this->isCli()) {
            echo $mes . PHP_EOL;
        } else {
            echo $mes . "<br />\n";
        }
        return;
    }


    /**
     * function for executing commands with php function exec
     * @param   string  $command to execute
     * @param   boolean $status_message display status message of exec command or not
     * @param   boolean $output echo output of command or not
     * @return  int     $ret the value returned by the shell script being
     *                  executed through exec()
     */
    public function execCommand(string $command, $status_message = 1, $output = 1)
    {
        $shell_output = array();
        exec($command . ' 2>&1', $shell_output, $ret);
        if ($ret == 0) {
            if ($status_message) {
                echo $this->colorOutput($this->getColorStatus('[OK]'), $this->colorSuccess);
                echo $command . PHP_EOL;

            }
        } else {
            if ($status_message) {
                echo $this->colorOutput($this->getColorStatus('[ERROR]'), $this->colorError);
                echo $command . PHP_EOL;
            }
        }

        if ($output) {
            echo $this->parseShellArray($shell_output);
        }

        return $ret;
    }

    /**
     * function for executing commands with php function system
     * this will always output the commands messages and errors
     * @param   string  $command to execute
     * @param   boolean $status_message display status message of exec command or not
     * @return  int     $ret the value returned by the shell script being
     *                  executed through exec()
     */
    public function systemCommand($command, $status_message = 1)
    {

        system($command . ' 2>&1', $ret);
        if ($ret == 0) {
            if ($status_message) {
                echo $this->colorOutput($this->getColorStatus('[OK]'), $this->colorSuccess);
                echo $command . PHP_EOL;

            }
        } else {
            if ($status_message) {
                echo $this->colorOutput($this->getColorStatus('[ERROR]'), $this->colorError);
                echo $command . PHP_EOL;
            }
        }

        return $ret;
    }

    /**
     * transform an array of output from exec into a single string
     * @param array $output
     * @return string $str
     */
    public function parseShellArray($output)
    {
        if (!is_array($output)) {
            return '';
        }
        $end_output = '';
        foreach ($output as $val) {
            $end_output .= $val . PHP_EOL;
        }
        return $end_output;
    }

    /**
     * a function for getting a confirm from command prompt
     * @param string $line a line to inform user what is going to happen
     * @param mixed $silence. If set we answer 'y' to all confirm readlines
     * @return int 1 on 'y' or 'Y' and 0 on anything else.
     */
    public function readlineConfirm($line = null, $set_silence = null)
    {

        if ($set_silence == 1) {
            return 1;
        }
        $str = $line;
        $str .= " Sure you want to continue? [Y/n]";
        $res = $this->readSingleline($str);
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
    public function abort($str = null)
    {
        if (isset($str)) {
            $str = $str . "\nAborting!";
        } else {
            $str = "Aborting!";
        }
        $this->echoMessage($this->colorOutput($str, $this->colorError));
        exit(16);
    }

    /**
     * Read a single line from stdin
     *
     * @param string $str the str to print to screen
     * @return string $out the input which readline reads
     */
    public function readSingleline($str)
    {
        echo $str;
        $out = "";
        $key = "";
        $key = fgetc(STDIN); // read from standard input (keyboard)
        while ($key != "\n") { // if the newline character has not yet arrived read another
            $out .= $key;
            $key = fread(STDIN, 1);
        }
        return $out;
    }
}