<?php

namespace Pouzor\MongoDBBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mongodb');

        $rootNode
            ->children()
                ->scalarNode('default_connection')->end()
            ->end();

        $this->addConnectionsSection($rootNode);
        $this->addDbIndexesSection($rootNode);

        return $treeBuilder;
    }


    /**
     * add Mongo Connections section
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addConnectionsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('manager')
                    ->defaultValue('document.manager')
                ->end()
            ->end()
            ->fixXmlConfig('connection')
            ->children()
                ->arrayNode('connections')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                    ->performNoDeepMerging()
                    ->children()
                        ->scalarNode('host')->defaultValue('127.0.0.1:27017')->end()
                        ->scalarNode('db')->end()
                        ->scalarNode('password')->end()
                        ->scalarNode('username')->end()
                        ->scalarNode('schema')->end()
                        ->arrayNode('options')
                            ->performNoDeepMerging()
                            ->children()
                                ->booleanNode('connect')->end()
                                ->integerNode('wtimeoutMS')->defaultValue(0)->end()
                                ->scalarNode('connectTimeoutMS')->end()
                                ->booleanNode('journal')->end()
                                ->enumNode('readPreference')
                                    ->values(array('primary', 'primaryPreferred', 'secondary', 'secondaryPreferred', 'nearest'))
                                ->end()
                                ->arrayNode('readPreferenceTags')
                                    ->performNoDeepMerging()
                                    ->prototype('array')
                                    ->beforeNormalization()
                                    // Handle readPreferenceTag XML nodes
                                    ->ifTrue(
                                        function ($v) {
                                            return isset($v['readPreferenceTag']);
                                        }
                                    )
                                    ->then(
                                        function ($v) {
                                            // Equivalent of fixXmlConfig() for inner node
                                            if (isset($v['readPreferenceTag']['name'])) {
                                                $v['readPreferenceTag'] = array($v['readPreferenceTag']);
                                            }

                                            return $v['readPreferenceTag'];
                                        }
                                    )
                                ->end()
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->scalarNode('replicaSet')->end()
                        ->scalarNode('socketTimeoutMS')->end()
                        ->booleanNode('ssl')->end()
                        ->scalarNode('w')->end()
                        ->scalarNode('wTimeoutMS')->end()
                    ->end()
                    ->validate()
                    ->ifTrue(
                        function ($v) {
                            return count($v['readPreferenceTags']) === 0;
                        }
                    )
                    ->then(
                        function ($v) {
                            unset($v['readPreferenceTags']);

                            return $v;
                        }
                    )
                    ->end()
                ->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }

    /**
     * add Mongo Connections section
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addDbIndexesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('database')
            ->children()
            ->arrayNode('databases')
            ->useAttributeAsKey('id')
            ->prototype('array')
            ->children()
            ->scalarNode('connection')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('db')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->scalarNode('configFile')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();
    }
}
