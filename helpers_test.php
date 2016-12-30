<?php


include_once "vendor/autoload.php";
include_once "cli/common.php";

use diversen\cli\common;

function echoStatus ($str, $color = 'yellow') {
    echo common::colorOutput($str, $color);
}

if (common::isCli()) {
    echoStatus("You are in a console\n");
}

if (common::isRoot()) {
    echoStatus("You are root\n");
} else {
    echoStatus ("You are not root\n");
}

$green_str = common::colorOutput('Test green', 'green');
echo $green_str . "\n";


echoStatus("Executing a command that does not exists (dudida)\n");
$res = common::execCommand('dudida');
echoStatus("$res is the result of the above operation\n", 'light_blue');

echoStatus("Executing a command that does not exists (dudida) - but without status message and the executed commands output\n");
$res = common::execCommand('dudida', 0, 0);
echoStatus("$res is the result of the above operation\n", 'light_blue');

echoStatus("Exectuing a command that that properbly exists (dir)\n");
$res = common::execCommand('dir');
echoStatus("$res is the result of the above operation\n", 'light_blue');

echoStatus("Exectuing a command that that properbly exists (dir) - but without status and command messages\n");
$res = common::execCommand('dir', 0, 0);
echoStatus("$res is the result of the above operation\n", 'light_blue');

echoStatus ("All is almost identically with common::systemCommand\n", 'bg_blue');
echoStatus ("But common::systemCommand will always echo output from the executed command", 'bg_blue');

echoStatus("\nEcho a red status message\n");
common::echoStatus("STATUS", 'light_green', 'A status about how we are doing it');

echoStatus("Prompt the user for input\n");
$line = common::readSingleline('Command will read a line: ');
echoStatus("You wrote $line\n");

echoStatus("Prompt the user for yes or no\n");
$answer = common::readlineConfirm('Are you sure you want to continue: ');
echoStatus("You answer evaluates to $answer\n");

echoStatus("Demand root. This is the last test\n");
$res = common::needRoot('You will need to be root to reach the end');
echoStatus("$res is the status of the common::needRoot method\n");
