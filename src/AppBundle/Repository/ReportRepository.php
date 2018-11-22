<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * ReportRepository
 */
class ReportRepository extends EntityRepository
{
	/**
	 * 通过report得到order实体
	 */
    public function findOrder($id)
    {
        $qb = $this->_em->getRepository('AppBundle:Order')->createQueryBuilder('o');
        return $qb->join('o.report', 'r')
                    ->where('r.id = ?1')
                    ->setParameter(1, $id)
                    ->getQuery()
                    ->getSingleResult()
        ;
    }

    /**
     * 获取report价格相关信息
     */
    public function findReportPrice($userId = null, $query = false)
    {
        $dateTime = new \DateTime();
        $dateTime->modify('-2 month');
        $qb = $this->createQueryBuilder('r')
            ->where('r.status = 1 and r.brandId is not null and r.seriesId is not null and r.modelId is not null and r.examedAt >= :dateTime ')
            ->setParameter('dateTime', $dateTime)
            ->orderBy('r.createdAt', 'ASC')
        ;

        return $qb->getQuery();
    }
}
