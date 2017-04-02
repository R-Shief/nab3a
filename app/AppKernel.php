<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new ConsulBundle\ConsulBundle(),
            new AppBundle\AppBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function boot()
	{
		if (true === $this->booted) {
			return;
		}

		$manager = (new \DL\ConsulPhpEnvVar\Builder\ConsulEnvManagerBuilder())
			->withOverwriteEvenIfDefined(true)
			->build();

		$mappings = [
			'TWITTER_CONSUMER_KEY' => 'twitter/consumer_key',
			'TWITTER_CONSUMER_SECRET' => 'twitter/consumer_secret',
			'TWITTER_ACCESS_TOKEN' => 'twitter/1/access_token',
			'TWITTER_ACCESS_TOKEN_SECRET' => 'twitter/1/access_token_secret',
		];

		$manager->getEnvVarsFromConsul($mappings);
		return parent::boot();
	}

	public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
		$loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
