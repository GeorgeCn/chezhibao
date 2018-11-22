<?php

namespace YYC\FoundationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('yyc_foundation');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->arrayNode('dsll')
                    ->children()
                        //大圣来了提供给我们对接api的url
                        ->scalarNode('url')->isRequired()->cannotBeEmpty()->end()
                        //大圣来了提供给我们对接api的私钥
                        ->scalarNode('private_key')->isRequired()->cannotBeEmpty()->end()
                        //大圣来了提供给我们对接api的合作伙伴标识
                        ->scalarNode('partner')->isRequired()->cannotBeEmpty()->end()
                    ->end()->end()
                ->arrayNode('cjd')
                    ->children()
                        //车鉴定提供给我们对接api的url
                        ->scalarNode('url')->isRequired()->cannotBeEmpty()->end()
                        //车鉴定提供给我们对接api的用户id
                        ->scalarNode('uid')->isRequired()->cannotBeEmpty()->end()
                        //车鉴定提供给我们对接api的密码
                        ->scalarNode('pwd')->isRequired()->cannotBeEmpty()->end()
                        //车鉴定提供给我们对接api的私钥
                        ->scalarNode('rsa_private_key')->isRequired()->cannotBeEmpty()->end()
                    ->end()->end()
                ->arrayNode('cbs')
                    ->children()
                        //查博士url
                        ->scalarNode('url')->isRequired()->cannotBeEmpty()->end()
                        //查博士uid
                        ->scalarNode('uid')->isRequired()->cannotBeEmpty()->end()
                        //查博士key
                        ->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                    ->end()->end()
                ->arrayNode('juhe')
                    ->children()
                        ->scalarNode('claims_key')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('maintence_key')->isRequired()->cannotBeEmpty()->end()
                    ->end()->end()
                ->arrayNode('ant_queen')
                    ->children()
                        ->scalarNode('partner_id')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('key')->isRequired()->cannotBeEmpty()->end()
                    ->end()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
