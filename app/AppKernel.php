<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new AppBundle\AppBundle(),
            new YouyicheBundle\YouyicheBundle(),

            new FOS\UserBundle\FOSUserBundle(),
            new Gregwar\CaptchaBundle\GregwarCaptchaBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),

            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new OldSound\RabbitMqBundle\OldSoundRabbitMqBundle(),

            new YYC\FoundationBundle\YYCFoundationBundle(),
            new Knp\Bundle\SnappyBundle\KnpSnappyBundle(),
        );

        // 为了修改RedisExtension的代码，做了如下丑陋的修改。
        $redisBundle = new Snc\RedisBundle\SncRedisBundle();
        $reflection = new ReflectionClass($redisBundle);
        $property = $reflection->getProperty("extension");
        $property->setAccessible(true);
        $property->setValue($redisBundle, new AppBundle\SncRedisExtension());
        $bundles[] = $redisBundle;

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new RaulFraile\Bundle\LadybugBundle\RaulFraileLadybugBundle();
            $bundles[] = new EasyCorp\Bundle\EasyDeployBundle\EasyDeployBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
