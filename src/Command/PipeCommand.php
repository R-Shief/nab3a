<?php

namespace App\Command;

use App\Console\LoggerHelper;
use App\Process\ChildProcess;
use App\Twitter\MessageEmitter;
use MKraemer\ReactPCNTL\PCNTL;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Stream\ReadableStreamInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class PipeCommand extends AbstractCommand
{
    protected function configure()
    {
        parent::configure();
        $this
          ->setName('pipe')
          ->setDescription('Connect to a streaming API endpoint and collect data')
          ->addOption('watch', null, InputOption::VALUE_NONE, 'watch for stream configuration changes and reconnect according to API rules')
          ->addOption('out', null, InputOption::VALUE_OPTIONAL, 'output', STDOUT)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loop = $this->container->get(LoopInterface::class);
        $pcntl = $this->container->get(PCNTL::class);

        // @todo

        // we need a timer that keeps track of the time the current connection
        // was started, because we must avoid connection churning.

        // filter parameters will change, we want to signal to the streaming
        // client that there it should reconnect, but if we don't accommodate
        // the fact that multiple changes could happen in a quick sequence, we'd
        // probably get blocked from the streaming API endpoints for too many
        // connection attempts.

        // When this app receives those errors, it manages them correctly,
        // but it still stupidly allows these situations to arise.
        // $timer = $watcher->watch($resource);

        switch ($output->getVerbosity()) {
            case OutputInterface::VERBOSITY_QUIET:
                $verbosity = '--quiet';
                break;
            case OutputInterface::VERBOSITY_VERBOSE:
                $verbosity = '--verbose';
                break;
            case OutputInterface::VERBOSITY_VERY_VERBOSE:
                $verbosity = '--verbose --verbose';
                break;
            case OutputInterface::VERBOSITY_DEBUG:
                $verbosity = '--verbose --verbose --verbose';
                break;
            default:
                $verbosity = '';
        }

        $process = $this->container
          ->get(ChildProcess::class)
          ->makeChildProcess('stream:read:twitter '.$input->getArgument('request').' '.$verbosity);

        assert($process instanceof Process);
        assert($process->stderr instanceof ReadableStreamInterface);
        assert($process->stdout instanceof ReadableStreamInterface);

        $this->attachListeners($process);

        $process->stderr->on('data', [$this->container->get(LoggerHelper::class), 'onData']);
        $process->stdout->on('data', [$this->container->get(MessageEmitter::class), 'onData']);
        $process->on('exit', function ($exitCode, $termSignal) use ($loop, $process) {
            $this->container->get('logger')->info(sprintf('child process pid %d exited with code %d signal %s', $process->getPid(), $exitCode, $termSignal));
            $loop->stop();
        });

        $pcntl->on(SIGTERM, function () use ($loop, $process) {
            $this->container->get('logger')->info(sprintf('process pid %d exited with code %d signal %s', posix_getpid(), 0, SIGTERM));
            if ($process->isRunning()) {
                $process->terminate();
            }
            $loop->stop();
        });

        $loop->run();

        return 1;
    }

    /**
     * @param Process $process
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function attachListeners(Process $process)
    {
        $dispatcher = $this->container->get('event_dispatcher');
        $listener = function () use ($process) {
            if ($process->isRunning()) {
                $process->terminate();
                usleep((int) (self::CHILD_PROC_TIMER * 1e6));
            }
            $this->container->get(LoopInterface::class)->stop();
        };
        $dispatcher->addListener(ConsoleEvents::ERROR, $listener);
        $dispatcher->addListener(ConsoleEvents::TERMINATE, $listener);
        register_shutdown_function($listener);
    }
}
