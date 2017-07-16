<?php

namespace App\Console;

use Clue\JsonStream\StreamingJsonParser;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoggerHelper implements EventEmitterInterface
{
    use ContainerAwareTrait;
    use EventEmitterTrait;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var StreamingJsonParser
     */
    private $parser;

    public function __construct(OutputInterface $output, StreamingJsonParser $parser)
    {
        $this->output = $output;
        $this->parser = $parser;
    }

    public function onData($chunk)
    {
        try {
            foreach ($this->parser->push($chunk) as $data) {
                $id = 'logger';
                if (isset($data['channel']) && $data['channel'] !== 'app') {
                    $id = 'monolog.logger.'.$data['channel'];
                }
                /** @var LoggerInterface $logger */
                $logger = $this->container->get($id);
                $logger->log($data['level'], $data['message'], $data['context']);
            }
        } catch (\UnexpectedValueException $e) {
            $this->output->write($chunk);
        }
    }
}
