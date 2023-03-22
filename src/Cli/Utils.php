<?php

declare(strict_types = 1);

namespace Diversen\Cli;

use PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;


/**
 * common helper function in CLI env.
 */
class Utils
{


    /**
     * Color of success
     */
    public string $colorSuccess = 'green';

    /**
     * Color of notice
     */
    public string $colorNotice = 'yellow';

    /**
     * Color of error
     */
    public string $colorError = 'red';

    /**
     * Stdout
     */
    private string $stdout = '';
    
    /**
     * Stderr
     */
    private string $stderr  = '';

    public function __construct(array $settings = [])
    {

        $this->colorSuccess = $settings['colorSuccess'] ?? $this->colorSuccess;
        $this->colorNotice = $settings['colorNotice'] ?? $this->colorNotice;
        $this->colorError = $settings['colorError'] ?? $this->colorError;
        
    }


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
        $output .= "Current command needs to be run as root. E.g. using sudo";

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
     * Color a string according to a color
     * Use the build in colors: 'notice', 'success', 'error' 
     * Or a color supported by the PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor class
     */
    public function colorOutput($str, $color = 'notice')
    {

        $use_color = null;
        if ($color == 'notice') {
            $use_color = $this->colorNotice;
        }

        if ($color == 'success') {
            $use_color = $this->colorSuccess;
        }

        if ($color == 'error') {
            $use_color = $this->colorError;
        }

        if (!$use_color) {
            $use_color = $color;
        }

        
        $consoleColor = new ConsoleColor();
        
        if ($consoleColor->isSupported()) {
            return $consoleColor->apply("$use_color", $str);
        }
        return $str;
    }

    /**
     * echo a colored status message
     */
    public function echoStatus(string $status, string $color, string $mes)
    {

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
     * Execute a command
     * @param   string  $command to execute
     * @param   boolean $status_message - display a status message (e.g. [OK] ... or [ERROR] ...)
     * @return  int     $ret the value returned by the shell script being
     *                  executed through exec()
     */
    public function exec(string $command, $status_message = 1)
    {

        $res = $this->procExec($command);
        if ($res == 0) {
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
        return $res;
    }

    public function readStdin() {
        $stdin = '';
        stream_set_blocking(STDIN, false);
        while (FALSE !== ($line = fgets(STDIN))) {
            $stdin .= $line;
         }

         return $stdin;
    }


    /**
     * Execute a command without any output
     * @param   string  $command to execute
     * @return  int     $ret the value returned by the shell script being
     *                  executed through exec()
     */
    public function execSilent($command) {
        return $this->exec($command, 0);
    }

    /**
     * Use proc_open. Easier to separate stdout and stderr
     * @link https://stackoverflow.com/a/25879953/464549
     */
    private function procExec($cmd) {
        $proc = proc_open($cmd,[
            1 => ['pipe','w'],
            2 => ['pipe','w'],
        ],$pipes);
        
        $this->stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $this->stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        
        return proc_close($proc);
    }

    /**
     * Return any message from commandline that are not errors
     */
    public function getStdout()
    {
        $stdout = $this->stdout;

        $this->stdout = '';
        return trim($stdout);
    }

    /**
     * Return any error messages. You should only use this if
     * the command that use with `Utils::exec` returns non 0 value 
     */
    public function getStderr()
    {
        $stderr = $this->stderr;
        $this->stderr = '';
        return trim($stderr);
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

        if (function_exists('readline')) {
            return readline($str);
        }

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
