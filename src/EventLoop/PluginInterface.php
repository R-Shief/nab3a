<?php

namespace App\EventLoop;

use React\EventLoop\LoopInterface;

/**
 * Interface PluginInterface
 * @package App\EventLoop
 */
interface PluginInterface
{
    /**
     * @param LoopInterface $loop
     * @return void
     */
    public function attach(LoopInterface $loop):void;
}
