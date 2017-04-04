<?php

namespace AppBundle\Command;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractCommand extends Command
{
    use LoggerAwareTrait;
    use ContainerAwareTrait;

    const CHILD_PROC_TIMER = 1e-3;

    /**
     * @var RequestInterface
     */
    protected $request;

    protected function configure()
    {
        $this->addArgument('request', InputArgument::REQUIRED, 'request key');
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $request = $input->getArgument('request');
        if (!$request) {
            return;
        }

        $kv = $this->container->get('consul.kv');

        // Using raw=true means we get the object without base64 encoding.
        $result = $kv->get($request, array('raw' => true));
        $response = $result->json();

        $this->request = new Request($response['method'], $response['uri'], $response['headers'], $response['body'], $response['version']);
    }
}
