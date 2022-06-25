<?php


include_once "vendor/autoload.php";

// You will not need the following include if you are autoloading 
// with the above include. This is just to make it easy to test.
include_once "Cli/Utils.php";

use Diversen\Cli\Utils;

$utils = new Utils();

if ($utils->isCli()) {
    echo $utils->colorOutput("You are in a console\n");
}

if ($utils->isRoot()) {
    echo $utils->colorOutput("You are root\n");
} else {
    echo $utils->colorOutput ("You are not root\n");
}

$green_str = $utils->colorOutput('Test green', 'green');
echo $green_str . "\n";


echo $utils->colorOutput("Executing a command that does not exists (dudida)\n");
$res = $utils->execCommand('dudida');
echo $utils->colorOutput("$res is the result of the above operation\n", 'light_blue');

echo $utils->colorOutput("Executing a command that does not exists (dudida) - but without status message and the executed commands output\n");
$res = $utils->execCommand('dudida', 0, 0);
echo $utils->colorOutput("$res is the result of the above operation\n", 'light_blue');

echo $utils->colorOutput("Exectuing a command that that properbly exists (ls)\n");
$res = $utils->execCommand('ls -l');
echo $utils->colorOutput("$res is the result of the above operation\n", 'light_blue');

echo $utils->colorOutput("Exectuing a command that that properbly exists (ls) - but without status and command messages\n");
$res = $utils->execCommand('ls -l', 0, 0);
echo $utils->colorOutput("$res is the result of the above operation\n", 'light_blue');

echo $utils->colorOutput ("All is almost identically with \$utils->systemCommand\n", 'bg_blue');
echo $utils->colorOutput ("But \$utils->systemCommand will always echo output from the executed command", 'bg_blue');

echo $utils->colorOutput("\nEcho a red status message\n");
echo $utils->colorOutput("STATUS ", 'red', 'A status about how we are doing!!!');

echo $utils->colorOutput("Prompt the user for a sinlge line of input\n");
$line = $utils->readSingleline('Command will read a line: ');
echo $utils->colorOutput("You wrote $line\n");

echo $utils->colorOutput("Prompt the user for yes or no\n");
$answer = $utils->readlineConfirm('Are you sure you want to continue: ');
echo $utils->colorOutput("You answer evaluates to $answer\n");

echo $utils->colorOutput("Demand root. This is the last test\n");
$res = $utils->needRoot('You will need to be root to reach the end');
echo $utils->colorOutput("$res is the status of the \$utils->needRoot method\n");