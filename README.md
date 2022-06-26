# minimal-cli-framework

A simple CLI framework with options for muliple sub-commands
loaded as classes. In order to implement a command into an
exisiting class you will just need to add two methods: `getCommand` and
`runCommand` - and then add an object to the framework.

# Install:

    composer require diversen/minimal-cli-framework

# Usage

Example using a single simple command: 

See [test/test.php](test/test.php) which has a single command (`echo`):

In to get help on the command `echo`

    php test/test.php echo --help

Usage could then be: 

    php test/test.php echo --strtoupper README.md

    php test/test.php echo --strtoupper README.md

Which will output the file in uppercase

# Helpers

Some common CLI helpers. 

See [test/utils_test.php](test/utils_test.php)

    php test/utils_test.php 

Colors are supported with [https://github.com/php-parallel-lint/PHP-Console-Color](https://github.com/php-parallel-lint/PHP-Console-Color)

License: MIT
