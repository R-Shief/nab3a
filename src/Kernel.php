<?php

namespace App;

use App\DependencyInjection\Compiler\AddConsoleCommandPass;
use App\DependencyInjection\Compiler\AttachPluginsCompilerPass;
use App\DependencyInjection\Compiler\StackMiddlewareCompilerPass;
use DL\ConsulPhpEnvVar\Builder\ConsulEnvManagerBuilder;
use React\EventLoop\LoopInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @codeCoverageIgnore
 */
final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function getCacheDir(): string
    {
        return dirname(__DIR__).'/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return dirname(__DIR__).'/var/logs';
    }

    /**
     * @return iterable|BundleInterface[]|\Generator
     */
    public function registerBundles()
    {
        $contents = require dirname(__DIR__).'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $confDir = dirname(__DIR__).'/config';
        $loader->load($confDir.'/packages/*'.self::CONFIG_EXTS, 'glob');
        if (is_dir($confDir.'/packages/'.$this->environment)) {
            $loader->load($confDir.'/packages/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        }
        $loader->load($confDir.'/container'.self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = dirname(__DIR__).'/config';
        if (is_dir($confDir.'/routing/')) {
            $routes->import($confDir.'/routing/*'.self::CONFIG_EXTS, '/', 'glob');
        }
        if (is_dir($confDir.'/routing/'.$this->environment)) {
            $routes->import($confDir.'/routing/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        }
        $routes->import($confDir.'/routing'.self::CONFIG_EXTS, '/', 'glob');
    }

    public function boot()
    {
        if (true === $this->booted) {
            return;
        }
        if ($this->environment !== 'test') {
            $manager = (new ConsulEnvManagerBuilder())
                ->withOverwriteEvenIfDefined(true)
                ->build();

            $manager->getEnvVarsFromConsul([
                'TWITTER_CONSUMER_KEY' => 'twitter/consumer_key',
                'TWITTER_CONSUMER_SECRET' => 'twitter/consumer_secret',
                'TWITTER_ACCESS_TOKEN' => 'twitter/1/access_token',
                'TWITTER_ACCESS_TOKEN_SECRET' => 'twitter/1/access_token_secret',
            ]);
        }
        parent::boot();
    }

    protected function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new StackMiddlewareCompilerPass());
        $container->addCompilerPass(new AddConsoleCommandPass('nab3a'));
        $container->addCompilerPass(new AttachPluginsCompilerPass(EventLoop\Configurator::class, 'event_loop.plugin', LoopInterface::class), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new AttachPluginsCompilerPass(Evenement\Configurator::class, 'evenement.plugin'), PassConfig::TYPE_BEFORE_REMOVING);
    }
}
