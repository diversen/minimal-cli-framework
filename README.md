# minimal-cli-framework

A simple CLI framework with options for multiple sub-commands
loaded as classes. In order to implement a command into an
exisiting class you will just need to add two methods: `getCommand` and
`runCommand` - and then add the object or the class to an instance
of the class `MinimalCli`.

# Install:

    composer require diversen/minimal-cli-framework

# Example usage

[src/EchoTest.php](src/EchoTest.php) is a command class
containing a single command `echo` and a couple of options.

This command is added to a simple program in [demos/example.php](demos/example.php)

In order to get help on the usage of the command `echo`:

    php demos/example.php echo --help

Put a file in uppercase: 

    php demos/example.php echo --up README.md

Or (shorthand): 

    php demos/example.php echo -u README.md

Which also will output the file in uppercase

# Utils

Some common CLI Utils. 

See [demos/utils_test.php](demos/utils_test.php)

    php demos/utils_test.php 

Colors are supported with [https://github.com/php-parallel-lint/PHP-Console-Color](https://github.com/php-parallel-lint/PHP-Console-Color)

License: MIT
