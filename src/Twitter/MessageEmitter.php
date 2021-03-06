<?php

namespace App\Twitter;

use Clue\JsonStream\StreamingJsonParser;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;

/**
 * Class MessageEmitter.
 *
 * This writable stream emits different types of messages from Twitter
 * Streaming API.
 */
class MessageEmitter implements EventEmitterInterface
{
    use EventEmitterTrait;

    /**
     * @var TypeGuesser
     */
    private $guesser;

    /**
     * @var StreamingJsonParser
     */
    private $parser;

    public function __construct(TypeGuesser $guesser, StreamingJsonParser $parser)
    {
        $this->guesser = $guesser;
        $this->parser = $parser;
    }

    /**
     * @param $data
     * @return void
     */
    public function onData($data) : void
    {
        // Blank lines are a keep-alive signal.
        if ($data === "\r\n") {
            $this->emit('keep-alive');

            return;
        }

        foreach ($this->parser->push($data) as $object) {
            if (!empty($object)) {
                $event = $this->guesser->getEventName($object);
                $this->emit($event, [$object]);
            }
        }
    }
}
