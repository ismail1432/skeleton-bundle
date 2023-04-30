<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->private()
        ->autowire()
        ->set('eniams.foo.such_service', FooService::class)
        ->tag('eniams.foo.tag')
    ;
    };