<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Diversen\MinimalCli;
use Diversen\EchoTest;

/**
 * Authorize test case.
 * Server needs to be running for this test to work.
 * ./serv
 */
final class MinimalCliTest extends TestCase
{

    public function testConstruct()
    {
        $minimal_cli = new MinimalCli();
        
        $this->assertInstanceOf(MinimalCli::class, $minimal_cli);

        $minimal_cli->addCommandClass('echo', EchoTest::class);

        // Only these public methods but should be made more testable
        // /$minimal_cli->setHeader("Program Test ECHO command");
        // $minimal_cli->runMain();
        
    }
    

}