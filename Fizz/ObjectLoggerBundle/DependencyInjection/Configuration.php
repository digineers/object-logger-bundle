<?php

namespace Fizz\ObjectLoggerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration definition.
 *
 * @author Richard Snijders <richard@fizz.nl>
 */
class Configuration implements ConfigurationInterface
{

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fizz_object_logger');
        $rootNode
            ->children()
                ->booleanNode('enable_default')
                    ->defaultTrue()
                ->end()
                ->booleanNode('save_user')
                    ->defaultTrue()
                ->end()
                ->arrayNode('ignored_fields')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('enabled_entities')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('discriminator_mapping')
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

}
