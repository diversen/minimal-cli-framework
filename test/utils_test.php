<?php

include_once "vendor/autoload.php";

// You will not need the following include if you are autoloading 
// with the above include. This is just to make it easy to test.
include_once "Cli/Utils.php";

use Diversen\Cli\Utils;

$settings = [
    'colorError' => 'red',
    'colorSuccess' => 'green',
    'colorNotice' => 'yellow',
];

$utils = new Utils($settings);

// Check if in console
if ($utils->isCli()) {
    echo ("You are in a console\n");
}

// Check if user is root
if ($utils->isRoot()) {
    echo $utils->colorOutput("You are root\n", 'notice');
} else {
    echo $utils->colorOutput ("You are not root\n", 'error');
}

// Built in colors
echo $notice_str = $utils->colorOutput("Built-in notice color\n", 'notice');
echo $success_str = $utils->colorOutput("Built-in success color\n", 'success');
echo $error_str = $utils->colorOutput("Built-in error color\n", 'error');

// Output a green string
$green_str = $utils->colorOutput('Test green', 'green');
echo $green_str . "\n";

// Exectuing a command and display a status message [OK] ls -l");
// Res is the shell result of the command. Normally 0 on success.
$res = $utils->exec('ls -l');

// Output from last command output\n";
echo $utils->getLastShellOutput() . "\n";

// Exectuing a command without any output
$res = $utils->execSilent('echo "Hello world"');
echo $utils->getLastShellOutput() . "\n";

// Prompt the user for input
$line = $utils->readSingleline('Command will read a line: ');
echo $utils->colorOutput("You wrote $line\n");

// Prompt for yes or no
$answer = $utils->readlineConfirm('Are you sure you want to continue: ');
echo $utils->colorOutput("You answer evaluates to $answer\n");

$res = $utils->needRoot("If you are not root we will now exit");
echo $utils->colorOutput("$res is the status of the \$utils->needRoot method\n");
