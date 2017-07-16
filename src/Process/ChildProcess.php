<?php

namespace App\Process;

use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

class ChildProcess
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * ChildProcess constructor.
     *
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @param string $cmd Command line to run
     * @param string $cwd Current working directory or null to inherit
     * @param array|null $env Environment variables or null to inherit
     * @param array $options Options for proc_open()
     *
     * @return Process
     * @throws \RuntimeException
     */
    public function makeChildProcess($cmd, $cwd = null, array $env = null, array $options = array()): Process
    {
        $cmd = 'exec php '.static::escapeArgument($_SERVER['argv'][0]).' --child '.$cmd;

        $process = new Process($cmd, $cwd, $env, $options);
        $process->start($this->loop);

        return $process;
    }

    /**
     * Escapes a string to be used as a shell argument.
     *
     * @param string $argument The argument that will be escaped
     *
     * @return string The escaped argument
     * @see \Symfony\Component\Process\Process::escapeArgument
     */
    private static function escapeArgument($argument): string
    {
        if ('\\' !== DIRECTORY_SEPARATOR) {
            return "'".str_replace("'", "'\\''", $argument)."'";
        }
        if ('' === $argument = (string) $argument) {
            return '""';
        }
        if (false !== strpos($argument, "\0")) {
            $argument = str_replace("\0", '?', $argument);
        }
        if (!preg_match('/[\/()%!^"<>&|\s]/', $argument)) {
            return $argument;
        }
        $argument = preg_replace('/(\\\\+)$/', '$1$1', $argument);

        return '"'.str_replace(array('"', '^', '%', '!', "\n"), array('""', '"^^"', '"^%"', '"^!"', '!LF!'), $argument).'"';
    }
}
