<?php
/**
 * Functions for bootstrapping.
 *
 * PHP version 7
 *
 * @category Symfony
 * @package  Nab3a
 * @author   Benjamin J Doherty <bjd@bangpound.org>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     <>
 */
namespace app {

    use DL\ConsulPhpEnvVar\Builder\ConsulEnvManagerBuilder;
    use Symfony\Component\Console\Input\ArgvInput;
    use Symfony\Component\Debug\Debug;
    use Symfony\Component\Dotenv\Dotenv;
    use Symfony\Component\Dotenv\Exception\PathException;

    /**
     * Enables debugging during bootstrap.
     *
     * @return void
     */
    function debug() : void
    {
        Debug::enable();
    }

    /**
     * Dotenv load.
     *
     * @return void
     */
    function dotenvLoad() : void
    {
        try {
            (new Dotenv())->load(__DIR__.'/../.env');
        } catch (PathException $e) {
            //
        }
    }

    /**
     * Run nab3a command application.
     *
     * @param array $argv Command line arguments.
     *
     * @return int
     */
    function nab3a(array $argv) : int
    {
        $input = new ArgvInput($argv);

        $env = $input->getParameterOption(
            ['--env', '-e'],
            getenv('SYMFONY_ENV') ?: 'dev'
        );
        $debug = getenv('SYMFONY_DEBUG') !== '0' &&
        !$input->hasParameterOption(['--no-debug', '']) && $env !== 'prod';

        if ($debug) {
            debug();
            dotenvLoad();
            consulLoad(include __DIR__.'/config/consul_mapping.php');
        }

        $kernel = new \AppKernel($env, $debug);
        $kernel->boot();
        $container = $kernel->getContainer();
        $kernel->shutdown();
        $application = $container->get('console.application');

        return $application->run(
            $container->get('console.input'),
            $container->get('console.output')
        );
    }

    /**
     * Load environment variables from consul.
     *
     * @param array $mappings Mappings of environment variables to keys.
     *
     * @return void
     */
    function consulLoad(array $mappings = array()) : void
    {
        $manager = (new ConsulEnvManagerBuilder())
            ->withOverwriteEvenIfDefined(true)
            ->build();

        $manager->getEnvVarsFromConsul($mappings);
    }
}
