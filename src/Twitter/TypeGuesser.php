<?php

namespace App\Twitter;

/**
 * Class TypeGuesser.
 */
class TypeGuesser
{
    const TYPE_UNKNOWN = 'unknown';

    /**
     * @param $data array[] single Twitter streaming message
     *
     * @return string message type
     */
    public function getEventName(array $data): string
    {
        // Twitter public stream messages that decode as objects with only
        // one property are events of that type. Events may also be objects
        // with multiple properties including an `event` property. Other
        // message objects many properties are a standard Tweet payload.
        // @see <https://dev.twitter.com/streaming/overview/messages-types>
        if (count($data) === 1) {
            return key($data);
        }

        if (isset($data['event'])) {
            return $data['event'];
        }

        if (count($data) > 1) {
            return 'tweet';
        }

        return self::TYPE_UNKNOWN;
    }
}
