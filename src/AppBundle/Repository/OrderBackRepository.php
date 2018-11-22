<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use \DateTime;

/**
 * OrderBackRepository
 */
class OrderBackRepository extends EntityRepository
{
    /**
     * 查找退单
     */
    public function findBackAudit($mixed = null, $startDate = null, $endDate = null)
    {
        $qb = $this->createQueryBuilder('ob')
            ->leftJoin('ob.examOrder', 'o')
            ->leftJoin('o.report', 'r')
            ->orderBy('ob.createdAt', 'DESC')
        ;

        if (!empty($mixed)) {
            $qb->andWhere('o.orderNo like :mixed or o.businessNumber like :mixed or r.vin like :mixed or r.brand like :mixed or r.series like :mixed or r.model like :mixed' )
                ->setParameter('mixed', '%'.$mixed.'%')
            ;
        }

        if (!empty($startDate)) {
            $qb->andWhere('ob.createdAt >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (!empty($endDate)) {
            $qb->andWhere('ob.createdAt <= :endDate')
                ->setParameter('endDate', date('Y/m/d/ H:i:s', strtotime('+1 day -1 second', strtotime($endDate))))
            ;
        }

        return $qb->getQuery();
    }
}
