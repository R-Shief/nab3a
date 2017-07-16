<?php

namespace App\Tests\Guzzle;

use App\Guzzle\Emitter;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class EmitterTest
 * @package App\Tests\Guzzle
 */
class EmitterTest extends TestCase
{
    /**
     * @var TestHandler
     */
    private $handler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp()
    {
        $this->handler = new TestHandler();
        $this->logger = new Logger('test', [$this->handler]);
    }

    public function testOnHeaders()
    {
        $emitter = new Emitter();
        $emitter->setLogger($this->logger);
        $handler = new MockHandler([
            new Response(),
        ]);

        $client = new Client([
            'on_headers' => [$emitter, 'onHeaders'],
            'handler' => new HandlerStack($handler),
        ]);

        $client->request('GET', 'http://localhost');

        $this->assertTrue($this->handler->hasDebugRecords());
    }

    public function testOnStats()
    {
        $emitter = new Emitter();
        $emitter->setLogger($this->logger);
        $handler = new MockHandler([
            new Response(),
        ]);

        $client = new Client([
            'on_stats' => [$emitter, 'onStats'],
            'handler' => new HandlerStack($handler),
        ]);

        $client->request('GET', 'http://localhost');

        $this->assertTrue($this->handler->hasDebugRecords());
    }
}
