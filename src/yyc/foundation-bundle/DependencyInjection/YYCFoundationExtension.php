<?php

namespace YYC\FoundationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class YYCFoundationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        //将config文件里面定义的配置写入parameters文件里面，方便调用
        $container->setParameter('yyc_foundation.dsll.url', $configs['0']['dsll']['url']);
        $container->setParameter('yyc_foundation.dsll.private_key', $configs['0']['dsll']['private_key']);
        $container->setParameter('yyc_foundation.dsll.partner', $configs['0']['dsll']['partner']);
        $container->setParameter('yyc_foundation.cjd.url', $configs['0']['cjd']['url']);
        $container->setParameter('yyc_foundation.cjd.uid', $configs['0']['cjd']['uid']);
        $container->setParameter('yyc_foundation.cjd.pwd', $configs['0']['cjd']['pwd']);
        $container->setParameter('yyc_foundation.cjd.rsa_private_key', $configs['0']['cjd']['rsa_private_key']);
        $container->setParameter('yyc_foundation.cbs.url', $configs['0']['cbs']['url']);
        $container->setParameter('yyc_foundation.cbs.uid', $configs['0']['cbs']['uid']);
        $container->setParameter('yyc_foundation.cbs.key', $configs['0']['cbs']['key']);
        $container->setParameter('yyc_foundation.juhe.claims_key', $configs['0']['juhe']['claims_key']);
        $container->setParameter('yyc_foundation.juhe.maintence_key', $configs['0']['juhe']['maintence_key']);
        $container->setParameter('yyc_foundation.ant_queen.partner_id', $configs['0']['ant_queen']['partner_id']);
        $container->setParameter('yyc_foundation.ant_queen.key', $configs['0']['ant_queen']['key']);
    }
}
