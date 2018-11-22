<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * 第一车BrandRepository
 */
class BrandRepository extends EntityRepository
{
    // /**
    //  * 查询第一车品牌
    //  */
    // public function findBrands()
    // {
    //     $qb = $this->createQueryBuilder('b')
    //         ->select('b.brandId', 'b.name')
    //     ;

    //     return $qb->getQuery()->getResult();
    // }
}
