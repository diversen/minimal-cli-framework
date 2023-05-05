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
        $minimal_cli->setTestMode();

        $this->assertInstanceOf(MinimalCli::class, $minimal_cli);

        $minimal_cli->addCommandClass('echo', EchoTest::class);
        $this->assertInstanceOf(EchoTest::class, $minimal_cli->commands['echo']);

        // No command specified
        $minimal_cli = new MinimalCli();
        $minimal_cli->addCommandClass('echo', EchoTest::class);
        $minimal_cli->setTestMode(argv: ['test.php']);
        $res = $minimal_cli->runMain();
        $this->assertEquals(0, $res);

        // Correct command given
        $minimal_cli = new MinimalCli();
        $minimal_cli->addCommandClass('echo', EchoTest::class);
        $minimal_cli->setTestMode(argv: ['test.php', 'echo', '--up', 'README.md']);
        $res = $minimal_cli->runMain();
        $this->assertEquals(0, $res);

        // Command that do not exists
        $minimal_cli = new MinimalCli();
        $minimal_cli->addCommandClass('echo', EchoTest::class);
        $minimal_cli->setTestMode(argv: ['test.php', 'do_not_exists', '--up', 'README.md']);
        $res = $minimal_cli->runMain();
        $this->assertEquals(1, $res);


        // Return value from command. Should be 12 as file does not exists
        $minimal_cli = new MinimalCli();
        $minimal_cli->addCommandClass('echo', EchoTest::class);
        $minimal_cli->setTestMode(argv: ['test.php', 'echo', '--up', 'WRONG_FILE.md']);
        $res = $minimal_cli->runMain();
        $this->assertEquals(12, $res);

        // Valid command alias 'e'
        $minimal_cli = new MinimalCli();
        $minimal_cli->addCommandClass('echo', EchoTest::class);
        $minimal_cli->setTestMode(argv: ['test.php', 'e', '--up', 'README.md']);
        $res = $minimal_cli->runMain();
        $this->assertEquals(0, $res);

        // Valid option alias '-u'
        $minimal_cli = new MinimalCli();
        $minimal_cli->addCommandClass('echo', EchoTest::class);
        $minimal_cli->setTestMode(argv: ['test.php', 'e', '-u', 'README.md']);
        $res = $minimal_cli->runMain();
        $this->assertEquals(0, $res);


        
    }
    

}