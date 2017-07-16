<?php

namespace App\Tests\Twitter;

use App\Twitter\MessageEmitter;
use App\Twitter\TypeGuesser;
use Clue\JsonStream\StreamingJsonParser;
use PHPUnit\Framework\TestCase;

class MessageEmitterTest extends TestCase
{
    public function testOnKeepAlive()
    {
        $guesser = $this->getMockBuilder(TypeGuesser::class)->getMock();
        $parser = $this->getMockBuilder(StreamingJsonParser::class)->getMock();
        $emitter = new MessageEmitter($guesser, $parser);

        $listenersCalled = 0;
        $emitter->on('keep-alive', function () use (&$listenersCalled) {
            $listenersCalled++;
        });
        $emitter->onData("\r\n");

        $this->assertEquals(1, $listenersCalled);
    }

    public function testOnEvent()
    {
        $guesser = $this->getMockBuilder(TypeGuesser::class)->getMock();
        $guesser->expects($this->once())->method('getEventName')->willReturn('special-event');
        $data = [['special-event' => true]];
        $parser = $this->getMockBuilder(StreamingJsonParser::class)->getMock();
        $parser->expects($this->once())->method('push')->willReturn($data);
        $emitter = new MessageEmitter($guesser, $parser);

        $listenersCalled = 0;
        $emitter->on('special-event', function ($event) use (&$listenersCalled) {
            $listenersCalled++;
            $this->assertEquals(['special-event' => true], $event);
        });
        $emitter->onData(json_encode($data[0]));

        $this->assertEquals(1, $listenersCalled);
    }

    public function testOnMultipleEvents()
    {
        $guesser = $this->getMockBuilder(TypeGuesser::class)->getMock();
        $guesser
            ->expects($this->exactly(3))
            ->method('getEventName')
            ->willReturn(
                'special-event',
                'special-event',
                'normal-event'
            );

        $data = [
            ['special-event' => true],
            ['special-event' => true],
            ['normal-event' => true]
        ];
        $parser = $this->getMockBuilder(StreamingJsonParser::class)->getMock();
        $parser->expects($this->once())->method('push')->willReturn($data);

        $emitter = new MessageEmitter($guesser, $parser);

        $specialListenersCalled = 0;
        $normalListenersCalled = 0;
        $emitter->on('special-event', function ($event) use (&$specialListenersCalled) {
            $specialListenersCalled++;
            $this->assertEquals(['special-event' => true], $event);
        });
        $emitter->on('normal-event', function ($event) use (&$normalListenersCalled) {
            $normalListenersCalled++;
            $this->assertEquals(['normal-event' => true], $event);
        });
        $emitter->onData(json_encode($data[0]));

        $this->assertEquals(2, $specialListenersCalled);
        $this->assertEquals(1, $normalListenersCalled);
    }
}
