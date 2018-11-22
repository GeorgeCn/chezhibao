<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ExamerLogRepository
 */
class ExamerLogRepository extends EntityRepository
{
    /**
     * 查询可能需要短信通知审核师的记录
     */
    public function findNeedSendMessageLog($reportId = null)
    {
        $qb = $this->createQueryBuilder('log')
            ->orderBy('log.startedAt', 'DESC')
        ;

        if ($reportId) {
            $qb->andWhere('log.reportId = :reportId')
                ->setParameter('reportId', $reportId)
            ;
        }

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * 保存时间倒序排列获取最近一条
     */
    public function findRecentMessageLog($reportId = null)
    {
        $qb = $this->createQueryBuilder('log')
            ->orderBy('log.savedAt', 'DESC')
        ;

        if ($reportId) {
            $qb->andWhere('log.reportId = :reportId')
                ->setParameter('reportId', $reportId)
            ;
        }

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }
}
