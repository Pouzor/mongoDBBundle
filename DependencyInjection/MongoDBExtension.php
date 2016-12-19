<?php

namespace Pouzor\MongoDBBundle\DependencyInjection;

use Pouzor\MongoDBBundle\DocumentManager\DocumentManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * Class MongoDBExtension
 * @package Pouzor\MongoDBBundle\DependencyInjection
 */
class MongoDBExtension  extends ConfigurableExtension
{
    // note that this method is called loadInternal and not load
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        foreach ($mergedConfig['connections'] as $name => $con) {

            $con['schema'] = Yaml::parse(
                file_get_contents($con['schema'])
            );

            $def = $container->register('document.manager.'.$name, DocumentManager::class);
            $def->addTag('document.manager',[ 'name' => $name]);
            $def->setArguments([$con, new Reference('logger')]);
            $def->addTag('monolog.logger', [ 'channel' => 'odm.mongo']);
        }

        if(isset($mergedConfig['default_connection']) and $container->hasDefinition('document.manager.'.$mergedConfig['default_connection'])){
            $container->setAlias('document.manager', 'document.manager.'.$mergedConfig['default_connection']);
        }
    }
}
