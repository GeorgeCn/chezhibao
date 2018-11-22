<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use AppBundle\Entity\PageView;


class PageViewRepository extends EntityRepository{
	public function create($params)
    {
		$em = $this->getEntityManager();
		$entry = new PageView();
		$entry->setUser($params['user'])
                ->setOrigin($params['origin'])
        		->setIp($params['ip'])
        		->setClientVersion($params['version'])
        		->setTime(new \DateTime())
                ->setDeepNum($params['deepnum'])
                ->setDraft($params['draft']);
		$em->persist($entry);
        $em->flush();
        return $entry;
    }
}
