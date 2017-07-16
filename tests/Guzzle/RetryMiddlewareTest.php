<?php

namespace App\Tests\Guzzle;

use App\Guzzle\RetryMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;

class RetryMiddlewareTest extends TestCase
{
    public function testChillRetry()
    {
        $handler = new MockHandler([
            new Response(420),
            new Response(),
        ]);
        $handlerStack = new HandlerStack($handler);
        $handlerStack->push(RetryMiddleware::retry());

        $client = new Client(['handler' => $handlerStack]);

        $stopwatch = new Stopwatch();
        $stopwatch->start('event');
        $client->request('GET', 'http://localhost');
        $duration = $stopwatch->stop('event')->getDuration();

        $this->assertGreaterThanOrEqual(60000, $duration);

        $handler = new MockHandler([
            new Response(420),
            new Response(420),
            new Response(),
        ]);
        $handlerStack = new HandlerStack($handler);
        $handlerStack->push(RetryMiddleware::retry());

        $client = new Client(['handler' => $handlerStack]);

        $stopwatch = new Stopwatch();
        $stopwatch->start('event');
        $client->request('GET', 'http://localhost');
        $duration = $stopwatch->stop('event')->getDuration();

        $this->assertGreaterThanOrEqual(180000, $duration);
    }

    public function testConnectRetry()
    {
        $handler = new MockHandler([
            function () {
                return false;
            },
            new Response(),
        ]);
        $handlerStack = new HandlerStack($handler);
        $handlerStack->push(RetryMiddleware::retry());

        $client = new Client(['handler' => $handlerStack]);

        $stopwatch = new Stopwatch();
        $stopwatch->start('event');
        $client->request('GET', 'http://localhost');
        $duration = $stopwatch->stop('event')->getDuration();

        $this->assertGreaterThanOrEqual(250, $duration);

        $handler = new MockHandler([
            function () {
                return false;
            },
            function () {
                return false;
            },
            new Response(),
        ]);
        $handlerStack = new HandlerStack($handler);
        $handlerStack->push(RetryMiddleware::retry());

        $client = new Client(['handler' => $handlerStack]);

        $stopwatch = new Stopwatch();
        $stopwatch->start('event');
        $client->request('GET', 'http://localhost');
        $duration = $stopwatch->stop('event')->getDuration();

        $this->assertGreaterThanOrEqual(500, $duration);
    }

    public function testHttpError()
    {
        $handler = new MockHandler([
            new Response(400),
            new Response(),
        ]);
        $handlerStack = new HandlerStack($handler);
        $handlerStack->push(RetryMiddleware::retry());

        $client = new Client(['handler' => $handlerStack]);

        $stopwatch = new Stopwatch();
        $stopwatch->start('event');
        $client->request('GET', 'http://localhost');
        $duration = $stopwatch->stop('event')->getDuration();

        $this->assertGreaterThanOrEqual(5000, $duration);

        $handler = new MockHandler([
            new Response(400),
            new Response(400),
            new Response(),
        ]);
        $handlerStack = new HandlerStack($handler);
        $handlerStack->push(RetryMiddleware::retry());

        $client = new Client(['handler' => $handlerStack]);

        $stopwatch = new Stopwatch();
        $stopwatch->start('event');
        $client->request('GET', 'http://localhost');
        $duration = $stopwatch->stop('event')->getDuration();

        $this->assertGreaterThanOrEqual(15000, $duration);
    }
}
