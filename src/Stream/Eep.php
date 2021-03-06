<?php

namespace App\Stream;

use Evenement\EventEmitterInterface;
use Psr\Log\LoggerInterface;
use App\Evenement;
use App\EventLoop;
use React\EEP\Window;
use React\EventLoop\LoopInterface;

/**
 * Class Eep.
 *
 * @property LoggerInterface $logger
 */
class Eep implements Evenement\PluginInterface, EventLoop\PluginInterface
{
    /**
     * @var Window\Periodic
     */
    private $statusCounter;

    /**
     * @var Window\Periodic
     */
    private $statusAverager;

    /**
     * @var Window\Periodic
     */
    private $idleTime;


    public function __construct(Window\Periodic $statusCounter, Window\Periodic $statusAverager, Window\Periodic $idleTime)
    {
        $this->statusCounter = $statusCounter;
        $this->statusAverager = $statusAverager;
        $this->idleTime = $idleTime;
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function attachEvents(EventEmitterInterface $emitter) : void
    {
        $emitter->on('tweet', [$this, 'tweetTimer']);
        $emitter->on('tweet', [$this, 'tweetCounter']);
    }

    /**
     * @param LoopInterface $loop
     */
    public function attach(LoopInterface $loop):void
    {
        $loop->addPeriodicTimer(.5, [$this, 'ticker']);
    }

    public function tweetTimer()
    {
        static $time;

        if ($time) {
            $ut = microtime(true);
            $v = abs($ut - $time);
            $this->idleTime->enqueue($v);
            $time = $ut;
        } else {
            $time = microtime(true);
        }
    }

    /**
     * @param $data
     */
    public function tweetCounter($data)
    {
        $this->statusCounter->enqueue($data);
        $this->statusAverager->enqueue($data);
    }

    public function ticker()
    {
        $this->statusCounter->tick();
        $this->statusAverager->tick();
        $this->idleTime->tick();
    }
}
