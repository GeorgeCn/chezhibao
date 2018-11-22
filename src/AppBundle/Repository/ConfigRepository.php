<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ConfigRepository
 */
class ConfigRepository extends EntityRepository
{
    /**
     * 获取公司名字
     */
    public function findCompanyNames()
    {
        $qb = $this->createQueryBuilder('c')
            ->select(array('c.company'))
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取公司id,名字
     */
    public function findCompanyNamesAndId()
    {
        $qb = $this->createQueryBuilder('c')
            ->select(array('c.id', 'c.company'))
        ;

        return $qb->getQuery()->getResult();
    }
}
