<?php

namespace AppBundle;

use Snc\RedisBundle\DependencyInjection\SncRedisExtension as Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * SncRedisExtension
 */
class SncRedisExtension extends Extension
{
    private function isDisable(ContainerBuilder $container)
    {
        $redis_server = $container->getParameter("redis_server");
        if (empty($redis_server) || $redis_server === "no") {
            return true;
        }
        return false;
    }

    protected function loadClient(array $client, ContainerBuilder $container)
    {
        if ($this->isDisable($container)) {
            return;
        }
        parent::loadClient($client, $container);
    }

    protected function loadSession(array $config, ContainerBuilder $container, XmlFileLoader $loader)
    {
        if ($this->isDisable($container)) {
            return;
        }
        //$container->getDefinition('session.storage.native')->replaceArgument(1, 'session.handler');
        //$container->getDefinition('session.storage.php_bridge')->replaceArgument(0, 'session.handler');
        $container->setAlias('session.handler', 'snc_redis.session.handler');
        parent::loadSession($config, $container, $loader);
    }

    protected function loadDoctrine(array $config, ContainerBuilder $container)
    {
        if ($this->isDisable($container)) {
            return;
        }
        parent::loadDoctrine($config, $container);
    }

    protected function loadMonolog(array $config, ContainerBuilder $container)
    {
        if ($this->isDisable($container)) {
            return;
        }
        parent::loadMonolog($config, $container);
    }

    protected function loadSwiftMailer(array $config, ContainerBuilder $container)
    {
        if ($this->isDisable($container)) {
            return;
        }
        parent::loadSwiftMailer($config, $container);
    }
}
