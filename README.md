# minimal-cli-framework

A simple CLI framework with options for multiple sub-commands
loaded as classes. In order to implement a command into an
exisiting class you will just need to add two methods: `getCommand` and
`runCommand` - and then add the object or the class to an instance
of the class `MinimalCli`.

# Install:

    composer require diversen/minimal-cli-framework

# Example usage

See [test/test.php](test/test.php) which is a program that 
has a single command `echo`:

In order to get help on the usage of the command `echo`:

    php test/test.php echo --help

Put a file in uppercase: 

    php test/test.php echo --up README.md

Or (shorthand): 

    php test/test.php echo -u README.md

Which also will output the file in uppercase

# Helpers

Some common CLI helpers. 

See [test/utils_test.php](test/utils_test.php)

    php test/utils_test.php 

Colors are supported with [https://github.com/php-parallel-lint/PHP-Console-Color](https://github.com/php-parallel-lint/PHP-Console-Color)

License: MIT
