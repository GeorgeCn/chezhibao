<?php

namespace AppBundle\Traits;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait DoctrineAwareTrait
{
    public function getDoctrineManager($connectionName = null)
    {
        return $this->getDoctrine()->getManager($connectionName);
    }

    public function getRepo($entityName, $connectionName = null)
    {
        return $this->getDoctrine()->getRepository($entityName, $connectionName);
    }

    public function flushDoctrineManager($connectionName = null)
    {
        $em = $this->getDoctrineManager($connectionName);
        $em->flush();
    }

    public function persistAndFlushDoctrineManager($entity, $connectionName = null)
    {
        $em = $this->getDoctrineManager($connectionName);
        $em->persist($entity);
        $em->flush();
    }
}
