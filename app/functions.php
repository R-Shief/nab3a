<?php

namespace app {

	use Symfony\Component\Console\Input\ArgvInput;
	use Symfony\Component\Debug\Debug;
    use Symfony\Component\Dotenv\Dotenv;
    use Symfony\Component\Dotenv\Exception\PathException;

	/**
	 * Enables debugging during bootstrap.
	 */
    function debug()
    {
        Debug::enable();
    }

    function dotenv_load()
    {
        try {
            (new Dotenv())->load(__DIR__.'/../.env');
        } catch (PathException $e) {
            //
        }
    }

	/**
	 * @param $argv
	 * @return int
	 */
    function nab3a($argv)
    {
    	$input = new ArgvInput($argv);

		$env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'dev');
		$debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(['--no-debug', '']) && $env !== 'prod';

		if ($debug) {
			debug();
			dotenv_load();
		}

        $kernel = new \AppKernel($env, $debug);
        $kernel->boot();
        $container = $kernel->getContainer();
        $kernel->shutdown();
        $application = $container->get('console.application');

        return $application->run($container->get('console.input'), $container->get('console.output'));
    }
}
