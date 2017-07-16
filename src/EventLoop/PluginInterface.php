<?php

namespace App\EventLoop;

use React\EventLoop\LoopInterface;

interface PluginInterface
{
    public function attach(LoopInterface $loop);
}
