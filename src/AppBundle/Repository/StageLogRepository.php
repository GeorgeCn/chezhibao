<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * StageLogRepository
 */
class StageLogRepository extends EntityRepository
{
    /**
     * 查询可能需要短信通知审核师的记录
     */
    public function findNeedSendMessageLog($reportId = null)
    {
        $qb = $this->createQueryBuilder('log')
            ->orderBy('log.createdAt', 'DESC')
        ;

        if ($reportId) {
            $qb->andWhere('log.reportId = :reportId')
                ->setParameter('reportId', $reportId)
            ;
        }

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * 创建时间倒序排列获取最近一条
     */
    public function findRecentMessageLog($reportId = null)
    {
        $qb = $this->createQueryBuilder('log')
            ->orderBy('log.createdAt', 'DESC')
        ;

        if ($reportId) {
            $qb->andWhere('log.reportId = :reportId')
                ->setParameter('reportId', $reportId)
            ;
        }

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * 根据报告id获取打点记录
     */
    public function findStageLog($reportId)
    {
        $qb = $this->createQueryBuilder('log')
            ->where('log.reportId = :reportId')
            ->setParameter('reportId', $reportId)
            ->orderBy('log.createdAt', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }
}
