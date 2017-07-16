<?php

namespace App\DependencyInjection\Compiler;

use App\Console\Application;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Console\Command\Command;

/**
 * Class AddConsoleCommandPass.
 *
 * @see \Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass
 */
class AddConsoleCommandPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $applicationServiceId;

    /**
     * @var string
     */
    private $namePrefix;

    /**
     * AddConsoleCommandPass constructor.
     * @param string $namePrefix
     * @param string $applicationServiceId
     */
    public function __construct($namePrefix = 'app', $applicationServiceId = Application::class)
    {
        $this->namePrefix = $namePrefix;
        $this->applicationServiceId = $applicationServiceId;
    }

    /**
     * @param ContainerBuilder $container
     * @throws InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $applicationDefinition = $container->getDefinition($this->applicationServiceId);
        $commandServices = $container->findTaggedServiceIds($this->namePrefix .'.console.command');

        foreach ($commandServices as $id => $tags) {
            $definition = $container->getDefinition($id);

            if (!$definition->isPublic()) {
                throw new InvalidArgumentException(sprintf('The service "%s" tagged "%s.console.command" must be public.', $id, $this->namePrefix));
            }

            if ($definition->isAbstract()) {
                throw new InvalidArgumentException(sprintf('The service "%s" tagged "%s.console.command" must not be abstract.', $id, $this->namePrefix));
            }

            $class = $container->getParameterBag()->resolveValue($definition->getClass());
            if (!is_subclass_of($class, Command::class)) {
                throw new InvalidArgumentException(sprintf('The service "%s" tagged "%s.console.command" must be a subclass of "Symfony\\Component\\Console\\Command\\Command".', $id, $this->namePrefix));
            }

            $applicationDefinition->addMethodCall('add', [new Reference($id)]);
        }
    }
}
