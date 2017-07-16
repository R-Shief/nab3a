<?php

namespace App\EventLoop;

use React\EventLoop\LoopInterface;

/**
 * Class Configurator
 * @package App\EventLoop
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
     * @param LoopInterface $loop
     */
    public function configure(LoopInterface $loop)
    {
        foreach ($this->plugins as $plugin) {
            $plugin->attach($loop);
        }
    }
}
