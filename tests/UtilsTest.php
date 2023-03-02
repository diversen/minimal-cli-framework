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

        $res = $utils->execSilent('thiscommanddoesnotexist');
        $this->assertEquals(127, $res);

        $this->assertEquals('', $utils->getStdout());

        // sh: 1: thiscommanddoesnotexist: not found'
        $this->assertStringContainsString('thiscommanddoesnotexist', $utils->getStderr());

        $res = $utils->exec("echo 'hello world'");
        $this->assertEquals(0, $res);

        $this->assertEquals('hello world', $utils->getStdout());

        

    }

}