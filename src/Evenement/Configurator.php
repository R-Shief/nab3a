<?php

namespace App\Evenement;

use Evenement\EventEmitterInterface;

/**
 * Class Configurator
 * @package App\Evenement
 */
class Configurator
{
    /**
     * @var PluginInterface[]
     */
    private $plugins;

    /**
     * Configurator constructor.
     * @param PluginInterface[] $plugins
     */
    public function __construct(array $plugins = array())
    {
        $this->plugins = $plugins;
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function configure(EventEmitterInterface $emitter)
    {
        foreach ($this->plugins as $plugin) {
            $plugin->attachEvents($emitter);
        }
    }
}
