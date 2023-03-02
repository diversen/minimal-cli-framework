<?php

declare(strict_types=1);

require_once "vendor/autoload.php";

use Diversen\MinimalCli;
use Diversen\EchoTest;

/**
 * Default settings for the CLI program. Only colors of output can be used
 * These are the default settings
 */
$settings = [
    'colorError' => 'red',
    'colorSuccess' => 'green',
    'colorNotice' => 'yellow',
];

$m = new MinimalCli($settings);

// Add class
$m->addCommandClass('echo', EchoTest::class);

// Program header. 
$m->setHeader("Program Test ECHO command");

// Run CLI program
$m->runMain();
