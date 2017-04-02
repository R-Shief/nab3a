<?php

namespace AppBundle\Command;

use Psr\Http\Message\ResponseInterface;
use AppBundle\Stream\TwitterStream;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReadStreamCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('stream:read:twitter')
            ->setDescription('Connect to a streaming API endpoint and collect data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->container->get('nab3a.twitter.guzzle.client');

        /** @var ResponseInterface $response */
        $response = $client->sendAsync($this->request, [
            'stream' => true,
        ])->wait();
        $stream = $response->getBody();

        while (!$stream->eof() && $stream->isReadable()) {
            $data = TwitterStream::handleData($stream);
            $output->write($data, false, OutputInterface::OUTPUT_RAW);
        }

        return 1;
    }
}
