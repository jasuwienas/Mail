<?php

namespace Jasuwienas\MailBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mail');
        $rootNode
            ->children()
                ->scalarNode('smtp_user')->defaultNull()->end()
                ->scalarNode('freshmail_api_host')->defaultNull()->end()
                ->scalarNode('freshmail_api_prefix')->defaultNull()->end()
                ->scalarNode('freshmail_api_api_key')->defaultNull()->end()
                ->scalarNode('freshmail_api_secret_key')->defaultNull()->end()
            ->end()
        ;
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
