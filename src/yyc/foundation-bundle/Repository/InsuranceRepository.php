<?php

namespace YYC\FoundationBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use \DateTime;

/**
 * InsuranceRepository
 */
class InsuranceRepository extends EntityRepository
{
    /**
     * 根据vin码查数据库中最近一次查询的记录
     */
    public function findLastByVin($vin = null)
    {
        $qb = $this->createQueryBuilder('i');

        if (!$vin) {
            return ;
        } else {
            $qb->andWhere('i.vin = :vin')
                ->setParameter('vin', $vin)
            ;
        }

        $qb->setMaxResults(1);
        $qb->orderBy('i.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据vin码查数据库中最近的历史记录
     */
    public function findRecentlyByVin($vin = null, $status = null)
    {
        $qb = $this->createQueryBuilder('i');

        if (!$vin) {
            return ;
        } else {
            $qb->andWhere('i.vin = :vin')
                ->setParameter('vin', $vin)
            ;
        }

        if ($status) {
            $qb->andWhere('i.status = :status')
                ->setParameter('status', $status)
            ;
        }

        // $qb->setMaxResults(3);
        $qb->orderBy('i.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据vin码查数据库当天是否有查询过的记录（失败的记录排除）
     */
    public function findTodayByVin($vin = null)
    {
        $qb = $this->createQueryBuilder('i');

        $today = date('Y-m-d');
        $dateTime = new DateTime($today);
        // 格式化下格式，不格式也可以，格式化后显得更清晰些
        $dateTime->format('Y-m-d H:i:s');
        $start = clone $dateTime;
        $end = $dateTime->modify('+1 days -1 seconds');

        if (!$vin) {
            return ;
        } else {
            $qb->andWhere('i.vin = :vin and i.createdAt >= :start and i.createdAt <= :end and i.status != 2')
                ->setParameter('vin', $vin)
                ->setParameter('start', $start)
                ->setParameter('end', $end)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
