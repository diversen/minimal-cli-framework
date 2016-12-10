<?php

include_once "vendor/autoload.php";
include_once "minimalCli.php";

use diversen\minimalCli;

class echoTest {
	// Optional help
	public function help () {
		return "some help";
	}

	// The command
	// The run command should get the option
	// From the commandline. You can then 
	// Make sub-command to your main command
	// ./cli test --test='hello world'
	public function run ($opts) {
		echo "Executing echo command\n";
	}

}

$echo = new echoTest;
$commands['echo'] = $echo;
$m = new minimalCli;
$m->commands = $commands;
$m->run();
$m->getTerminalWidth();
