<?php

namespace Eniams\FooBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

// More details on https://symfony.com/doc/current/bundles/configuration.html#using-the-abstractbundle-class
class FooBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                    ->integerNode('client_id')->end()
                    ->scalarNode('client_secret')->end()
                ->end()
            ->end()
        ->end()
        ;
    }

    public function loadExtension(array $configs, ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
    {
        // Load the services.php file in the same bundle.
        $loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__.'/Resources/config'));
        $loader->load('services.php');

        // Get the configuration `client_id` `client_secret` set by the User who loads the bundle.
        $clientId = $configs['client_id'];
        $clientSecret = $configs['client_secret'];

        if ('' === $clientId || '' === $clientSecret) {
            throw new \InvalidArgumentException(sprintf('You must provide a valid client id and secret for twitter'));
        }

        // Create a service definition for the `FooService` class.
        // A service definition is a PHP object that contains all the configuration information about the service to create.
        $containerBuilder->setDefinition('eniams.foo.such_service', new Definition(FooService::class))
            ->setArgument(0, $clientId)
            ->setArgument(1, $clientSecret)
        ;

        // Register the `FooInterface` interface for autoconfiguration.
        // So all classes that implement the interface `FooInterface::class`  will be automatically tagged with `eniams.foo.tag`.
        $containerBuilder->registerForAutoconfiguration(FooInterface::class)->addTag('eniams.foo.tag');

        // Create a service definition for the `DummyFactory` class.
        // Injected with all services tagged with `eniams.foo.tag`.
        $containerBuilder->setDefinition('eniams.foo.dummy_factory', new Definition(DummyFactory::class))
            ->setArguments([tagged_iterator('eniams.foo.tag')])
        ;

        // create a service definition for the `FooListener` class
        // that will be tagged with `kernel.event_subscriber` and
        // have a service injected: `eniams.foo.dummy_factory`
        $containerConfigurator
            ->services()
            ->set('eniams.foo.listener', FooListener::class)
            ->tag('kernel.event_subscriber')
            ->args([
                new Reference('eniams.foo.dummy_factory'),
            ])
        ;
    }

}