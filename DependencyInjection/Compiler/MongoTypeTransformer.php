<?php

namespace Pouzor\MongoDBBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class MongoTypeTransformer implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $ids = $container->findTaggedServiceIds('mongo.type_transformer');

        $references = [];

        foreach($ids as $id => $tags)
        {
            foreach($tags as $tag => $attributes)
            {
                $references[] = new Reference($id);
            }
        }

        $managers = $container->findTaggedServiceIds('document.manager');

        foreach($managers as $id => $tags)
        {
            $def = $container->getDefinition($id);

            foreach($references as $ref){
                $def->addMethodCall('addTransformer', [ $ref]);
            }
        }
    }
}
