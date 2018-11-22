<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ProvinceRepository
 */
class ProvinceRepository extends EntityRepository
{
    public function findProvince($name)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.name like :name' )
            ->setParameter('name', '%'.$name.'%')
        ;

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }
}
