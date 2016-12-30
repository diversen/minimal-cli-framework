# minimal-cli-framework

A very simple CLI framework with options for muliple sub-commands
loaded as classes. In order to implement a command into an
exisiting class you will just need to add two methods: `getHelp` and
`runCommand` - and then add a class object to the framework.

# Install:

    composer require diversen/minimal-cli-framework

# Usage

It consists of two files. A file containing the framework and a file containing some 
common CLI helpers. 

Example using a single simple command: 

See [test.php](test.php)

# Helpers

See [test_helpers.php](test_helpers.php)

Colors are supported with [https://github.com/JakubOnderka/PHP-Console-Color](https://github.com/JakubOnderka/PHP-Console-Color)

License: MIT
