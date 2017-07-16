<?php

namespace App\Stream;

use GuzzleHttp\Psr7;
use Psr\Http\Message\StreamInterface;

class TwitterStream
{
    /**
     * Read one entry from a length delimited stream.
     *
     * @param StreamInterface $stream
     * @param null $maxLength
     *
     * @return string
     * @throws \RuntimeException
     * @suppress PhanTypeMismatchArgument
     */
    public static function handleData(StreamInterface $stream, $maxLength = null): string
    {
        do {
            $data = Psr7\readline($stream, $maxLength);
        } while ($data === '');

        $length = (int) trim($data);

        if ($length) {
            $data = $stream->read($length);
        }

        return $data;
    }
}
