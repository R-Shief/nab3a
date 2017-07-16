<?php

namespace App\RabbitMq;

use App\Evenement\PluginInterface;
use Evenement\EventEmitterInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

class EnqueueTweetPlugin implements PluginInterface
{
    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @var string
     */
    private $routingKey;

    /**
     * @var array
     */
    private $additionalProperties;

    public function __construct(ProducerInterface $producer, $routingKey = '', array $additionalProperties = array())
    {
        $this->producer = $producer;
        $this->routingKey = $routingKey;
        $this->additionalProperties = $additionalProperties;
    }

    /**
     * @param EventEmitterInterface $emitter
     */
    public function attachEvents(EventEmitterInterface $emitter): void
    {
        $emitter->on('tweet', [$this, 'enqueue']);
    }

    /**
     * @param $data
     */
    public function enqueue($data) : void
    {
        $data = json_encode($data);
        $this->producer->publish($data, $this->routingKey, $this->additionalProperties);
    }
}
