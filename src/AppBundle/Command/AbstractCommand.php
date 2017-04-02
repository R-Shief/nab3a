<?php

namespace AppBundle\Command;

use AppBundle\Entity\StreamParameters;
use AppBundle\MergeStreamParameters;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->addArgument('request', InputArgument::REQUIRED, 'request');
        parent::configure();
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $kv = $this->container->get('consul.kv');
        $request = $input->getArgument('request');

        if ($request) {
            $result = $kv->get($request, array('raw' => true));
            $response = $result->json();
            $this->request = new Request($response['method'], $response['uri'], $response['headers'], $response['body'], $response['version']);
        }

        if (!isset($result)) {
            throw new InvalidArgumentException();
        }



        //    	$kv = $this->container->get('kv')
//        $client = $this->container->get('nab3a.guzzle.client.params');
//        $serializer = $this->container->get('serializer');
//        $merger = new MergeStreamParameters();
//        $params = new StreamParameters();
//        foreach ((array) $input->getOption('stream') as $stream) {
//            if (ctype_digit($stream)) {
//                $response = $client->get('stream/'.$stream);
//                $streamParameter = $serializer->deserialize($response->getBody(), StreamParameters::class, 'json');
//                $params = $merger->merge([$streamParameter, $params]);
//            } elseif ($stream === 'enabled') {
//                $response = $client->get('stream', ['query' => ['enabled' => 1]]);
//                $streamParameter = $serializer->deserialize($response->getBody(), StreamParameters::class .'[]', 'json');
//                $params = $merger->merge($streamParameter);
//            }
//        }
    }
}
