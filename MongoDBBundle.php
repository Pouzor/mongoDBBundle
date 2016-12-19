<?php

namespace Pouzor\MongoDBBundle;


use Pouzor\MongoDBBundle\DependencyInjection\Compiler\MongoTypeTransformer;
use Pouzor\MongoDBBundle\DependencyInjection\MongoDBExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MongoDBBundle
 * @package Pouzor\MongoDBBundle
 */
class MongoDBBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new MongoDBExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new MongoTypeTransformer());
    }
}
