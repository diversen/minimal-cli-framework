<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Authorize test case.
 * Server needs to be running for this test to work.
 * ./serv
 */
final class UtilsTest extends TestCase
{

    public function testIsRoot()
    {
        $utils = new \Diversen\Cli\Utils();
        $this->assertFalse($utils->isRoot());
    }

    public function testColor()
    {
        $utils = new \Diversen\Cli\Utils();
        $this->assertEquals('green', $utils->colorSuccess);
    }


    public function testIsCli()
    {
        $utils = new \Diversen\Cli\Utils();
        $this->assertTrue($utils->isCli());
    }

    public function testColorOutput()
    {
        $utils = new \Diversen\Cli\Utils();
        $this->assertEquals('green', $utils->colorSuccess);
    }

    public function testExec()
    {
        $utils = new \Diversen\Cli\Utils();
        $res = $utils->execSilent('ls');

        $this->assertEquals(0, $res);

        $res = $utils->execSilent('this_command_does_not_exist');

        // Read exit code from weird command: 'this_command_does_not_exist'
        $this->assertEquals(127, $res);

        // STDOUT should be empty
        $this->assertEquals('', $utils->getStdout());

        // But STDERR should contain the error message
        $this->assertStringContainsString('this_command_does_not_exist', $utils->getStderr());

        // This test should be a legit command
        $res = $utils->exec("echo 'hello world'");

        // And exit code should be 0
        $this->assertEquals(0, $res);

        // And STDOUT should contain the string
        $this->assertEquals('hello world', $utils->getStdout());

        // And STDERR should be empty
        $this->assertEquals('', $utils->getStderr());

        

    }

}