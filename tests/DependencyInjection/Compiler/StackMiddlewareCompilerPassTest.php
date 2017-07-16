<?php

namespace App\Tests\DependencyInjection\Compiler;

use App\DependencyInjection\Compiler\StackMiddlewareCompilerPass;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class StackMiddlewareCompilerPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();
        $container->register('client', Client::class);
        $definition = $container->register('middleware', \Closure::class);
        $definition->addTag('guzzle.middleware', [
            'client' => 'client',
            'middleware_name' => 'test',
        ]);
        $definition = $container->register('middleware_before', \Closure::class);
        $definition->addTag('guzzle.middleware', [
            'client' => 'client',
            'middleware_name' => 'test_before',
            'before' => 'test',
        ]);
        $definition = $container->register('middleware_after', \Closure::class);
        $definition->addTag('guzzle.middleware', [
            'client' => 'client',
            'middleware_name' => 'test_after',
            'after' => 'test',
        ]);
        $pass = new StackMiddlewareCompilerPass();
        $pass->process($container);

        /** @var Definition $handlerDefinition */
        $handlerDefinition = $container->getDefinition('client')->getArgument(0)['handler'];
        assert($handlerDefinition instanceof Definition);

        $this->assertTrue($container->hasDefinition('client'));
        $this->assertInstanceOf(Definition::class, $handlerDefinition);
        $this->assertCount(3, $handlerDefinition->getMethodCalls());
        $this->assertTrue($handlerDefinition->hasMethodCall('push'));
        $this->assertTrue($handlerDefinition->hasMethodCall('before'));
        $this->assertTrue($handlerDefinition->hasMethodCall('after'));
    }

    public function testHasHandlerDefinitionProcess()
    {
        $container = new ContainerBuilder();
        $definition = $container->register('client', Client::class);
        $definition->addArgument([
            'handler' => new Definition(),
        ]);
        $definition = $container->register('middleware', \Closure::class);
        $definition->addTag('guzzle.middleware', [
            'client' => 'client',
            'middleware_name' => 'test',
        ]);

        $pass = new StackMiddlewareCompilerPass();
        $pass->process($container);

        /** @var Definition $handlerDefinition */
        $handlerDefinition = $container->getDefinition('client')->getArgument(0)['handler'];
        assert($handlerDefinition instanceof Definition);

        $this->assertTrue($container->hasDefinition('client'));
        $this->assertInstanceOf(Definition::class, $handlerDefinition);
        $this->assertCount(1, $handlerDefinition->getMethodCalls());
        $this->assertTrue($handlerDefinition->hasMethodCall('push'));
    }

    public function testProcessNoClient()
    {
        $container = new ContainerBuilder();
        $definition = $container->register('middleware', \Closure::class);
        $definition->addTag('guzzle.middleware', [
            'client' => 'client',
            'middleware_name' => 'test',
        ]);
        $pass = new StackMiddlewareCompilerPass();
        $pass->process($container);

        $this->assertFalse($container->hasDefinition('client'));
    }
}
