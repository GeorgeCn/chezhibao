<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * CityRepository
 */
class CityRepository extends EntityRepository
{
    public function findCity($name)
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.name like :name' )
            ->setParameter('name', '%'.$name.'%')
        ;

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }
}
