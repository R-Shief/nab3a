<?php

namespace App\Evenement;

use Evenement\EventEmitterInterface;

/**
 * Interface PluginInterface
 * @package App\Evenement
 */
interface PluginInterface
{
    /**
     * @param EventEmitterInterface $emitter
     * @return void
     */
    public function attachEvents(EventEmitterInterface $emitter):void;
}
