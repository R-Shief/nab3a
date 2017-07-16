<?php

namespace App\Tests\Twitter;

use App\Twitter\TypeGuesser;
use PHPUnit\Framework\TestCase;

/**
 * Class TypeGuesserTest
 * @package App\Tests\Twitter
 * @coversDefaultClass \App\Twitter\TypeGuesser
 */
class TypeGuesserTest extends TestCase
{
    /**
     * @covers ::getEventName
     */
    public function testGetEventName()
    {
        $typeGuesser = new TypeGuesser();

        // Tweets have multiple keys but none named `event`.
        $data = ['created_at' => 'some date', 'author' => []];
        $this->assertEquals('tweet', $typeGuesser->getEventName($data));

        // Some events have multiple keys and one named `event`.
        $data = ['event' => 'key', 'be' => 'preferred', 'when' => 'it', 'is' => 'set'];
        $this->assertEquals('key', $typeGuesser->getEventName($data));

        // Most events have a single property.
        $data = ['normal' => 'event messages have only one property'];
        $this->assertEquals('normal', $typeGuesser->getEventName($data));

        // This is bad.
        $data = [];
        $this->assertEquals(TypeGuesser::TYPE_UNKNOWN, $typeGuesser->getEventName($data));
    }
}
