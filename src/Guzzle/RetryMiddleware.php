<?php

namespace App\Guzzle;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RetryMiddleware
{
    /**
     * @var callable
     */
    private $nextHandler;

    public function __construct(callable $nextHandler)
    {
        $this->nextHandler = $nextHandler;
    }

    /**
     * @param RequestInterface $request
     * @param array $options
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, array $options)
    {
        $prev = $this->nextHandler;

        foreach (array_reverse(self::retryMiddlewares()) as $fn) {
            $prev = $fn[0]($prev);
        }

        return $prev($request, $options);
    }

    /**
     * @return callable|\Closure
     */
    public static function retry(): callable
    {
        return function (callable $handler) {
            return new static($handler);
        };
    }

    /**
     * @return array
     */
    private static function retryMiddlewares(): array
    {
        return array(
            [
                Middleware::retry(
                    self::connectExceptionDecider(),
                    self::linearDelay(250, 16000)
                ),
                'connect_error'
            ],
            [
                Middleware::retry(
                    self::httpErrorDecider(),
                    self::exponentialDelay(5000, 320000)
                ),
                'http_error'
            ],
            [
                Middleware::retry(
                    self::rateLimitErrorDecider(),
                    self::exponentialDelay(60000)
                ),
                'rate_limit'
            ],
        );
    }

    /**
     * @return callable
     */
    public static function connectExceptionDecider(): callable
    {
        return function (int $retries, Psr7\Request $request, Psr7\Response $response = null, $error = null) {
            return !(bool)$response || $error instanceof ConnectException;
        };
    }

    /**
     * @return callable
     */
    public static function rateLimitErrorDecider(): callable
    {
        return function (int $retries, Psr7\Request $request, Psr7\Response $response = null, $error = null) {
            return $response && $response->getStatusCode() === 420;
        };
    }

    /**
     * @return callable
     */
    public static function httpErrorDecider(): callable
    {
        return function (int $retries, Psr7\Request $request, Psr7\Response $response = null, $error = null) {
            return $response && $response->getStatusCode() >= 400;
        };
    }

    /**
     * @param $base
     * @param int $maxDelay
     * @return callable
     */
    public static function exponentialDelay(int $base, int $maxDelay = 0): callable
    {
        return function (int $retries) use ($base, $maxDelay) {
            $delay = \GuzzleHttp\RetryMiddleware::exponentialDelay($retries) * $base;

            return $maxDelay ? min($delay, $maxDelay) : $delay;
        };
    }

    /**
     * @param $base
     * @param int $maxDelay
     * @return callable
     */
    public static function linearDelay(int $base, int $maxDelay = 0): callable
    {
        /**
         * @param $retries
         * @return int
         */
        return function (int $retries) use ($base, $maxDelay) {
            $delay = $retries * $base;

            return $maxDelay ? min($delay, $maxDelay) : $delay;
        };
    }
}
