<?php
namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use \DateTime;
use AppBundle\Entity\Report;
use AppBundle\Entity\Order;

/**
 * OrderRepository
 */
class OrderRepository extends EntityRepository
{
    /**
     * 草稿箱订单
     */
    public function findOrderDraft($userId = null, $query = false)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.loadOfficer', 'u')
            ->where('o.status = 0 and o.lastBack is null and o.disable != 1')
            ->orderBy('o.createdAt', 'ASC')
        ;

        if (!empty($userId)) {
            $qb->andWhere('u.id = :userId' )
                ->setParameter('userId', $userId)
            ;
        }

        if ($query) {
            return $qb->getQuery();
        } 

        return $qb->getQuery()->getResult();
    }

	/**
	 * 已提交订单
	 */
    public function findOrderSubmitted($userId = null, $mixed = null, $status = null, $startDate = null, $endDate = null, $query = false)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->leftJoin('o.loadOfficer', 'u')
            ->where('o.status != 0 and o.disable != 1')
            ->orderBy('o.submitedAt', 'DESC')
        ;

        if (!empty($userId)) {
            $qb->andWhere('u.id = :userId')
                ->setParameter('userId', $userId)
            ;
        }

        if (!empty($mixed)) {
            $qb->andWhere('o.orderNo like :mixed or o.businessNumber like :mixed or r.vin like :mixed or r.brand like :mixed or r.series like :mixed or r.model like :mixed' )
                ->setParameter('mixed', '%'.$mixed.'%')
            ;
        }

        // 3代表下拉列表默认的全部状态
        if (!empty($status) && $status != 3) {
            // 将页面中的10转换为真实的0状态(report表里面的0),目的是让页面默认显示不出问题。
            if ($status == 10 ) {
                $qb->andWhere('(o.status = 1 and r.status = 0) or (o.status = 2 and r.status = 0) or o.report is null')
                ;
            } else {
                $qb->andWhere('r.status = :status')
                    ->setParameter('status', $status)
                ;
            }
        }

        if (!empty($startDate)) {
            $qb->andWhere('o.submitedAt >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (!empty($endDate)) {
            $qb->andWhere('o.submitedAt <= :endDate')
                ->setParameter('endDate', date('Y/m/d/ H:i:s', strtotime('+1 day -1 second', strtotime($endDate))))
            ;
        }

        if ($query) {
            return $qb->getQuery();
        }

        return $qb->getQuery()->getResult();
    }

	/**
	 * 已退回订单
	 */
    public function findOrderBack($userId = null, $query = false)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.loadOfficer', 'u')
            ->leftJoin('o.lastBack', 'b')
            ->where('o.lastBack is not null and o.status = 0 and o.disable != 1')
            ->orderBy('b.createdAt', 'DESC')
        ;

        if (!empty($userId)) {
            $qb->andWhere('u.id = :userId' )
                ->setParameter('userId', $userId)
            ;
        }

        if ($query) {
            return $qb->getQuery();
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 订单任务列表, 可根据用户的type类型来查
     */
    public function findTask($query = false, $type = null, $status = null, $stage = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.parent', 'p')
            ->leftJoin('o.loadOfficer', 'u')
            ->leftJoin('o.report', 'r')
            ->leftJoin('o.lastBack', 'b')
            ->where('o.status = :status and o.disable != 1 and (o.report is null or r.hplExaming != 1) and (o.parent is null or p.status = :parentStatus)')
            ->setParameters(['parentStatus' => Order::STATUS_DONE, 'status' => Order::STATUS_EXAM])
            ->orderBy('o.submitedAt', 'ASC')
        ;

        if ($type) {
            $qb->andWhere('u.type = :type' )
                ->setParameter('type', $type)
            ;
        }

        if ($status) {
            if ('1' === $status) {
                $qb->andWhere('o.lastBack is null and o.lastBack is null and r.hplReason is null');
            } elseif ('2' === $status) {
                $qb->andWhere('o.lastBack is not null');
            } else {
                $qb->andWhere('o.report is not null and r.hplReason is not null');
            }
        }

        if ($stage) {
            if ('20' === $stage) {
                $qb->andWhere('o.report is null');
            } elseif ('30' === $stage) {
                $qb->andWhere('o.report is not null');
            } else {
                $qb->andWhere('r.stage = :stage')
                    ->setParameter('stage', $stage)
                ;
            }
        }

        if ($query) {
            return $qb->getQuery();
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 高价车复审列表
     */
    public function findRecheck($query = false, $companyName = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->leftJoin('o.company', 'c')
            ->where('o.status in (:status) and r.status = 0 and o.disable != 1 and r.hplExaming = 1')
            ->setParameters(['status' => [Order::STATUS_EXAM, Order::STATUS_RECHECK]])
            ->orderBy('o.createdAt', 'ASC')
        ;

        if ($companyName) {
            $qb->andWhere('c.company = :companyName')->setParameter('companyName', $companyName);
        }

        if ($query) {
            return $qb->getQuery();
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 高价车复审列表
     */
    public function findAllRecheckCount($companyName = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('o.report', 'r')
            ->leftJoin('o.company', 'c')
            ->where('o.status in (:status) and r.status = 0 and o.disable != 1 and r.hplExaming = 1')
            ->setParameters(['status' => [Order::STATUS_EXAM, Order::STATUS_RECHECK]])
            ->orderBy('o.createdAt', 'ASC')
        ;

        if ($companyName) {
            $qb->andWhere('c.company = :companyName')->setParameter('companyName', $companyName);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }


    /**
     * 已复核的订单列表
     */
    public function findOrderChecked($mixed = null, $status = null, $startDate = null, $endDate = null, $companyName = null, $query = false)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.status = 2 and o.disable != 1')
            ->leftJoin('o.report', 'r')
            ->leftJoin('o.company', 'c')
            ->orderBy('r.examedAt', 'DESC')
        ;

        if (!empty($mixed)) {
            $qb->andWhere('o.orderNo like :mixed or o.businessNumber like :mixed or r.vin like :mixed or r.brand like :mixed or r.series like :mixed or r.model like :mixed' )
                ->setParameter('mixed', '%'.$mixed.'%')
            ;
        }

        if (!empty($status) && $status != 3) {
            $qb->andWhere('r.status = :status' )
                ->setParameter('status', $status)
            ;
        }

        if (!empty($startDate)) {
            $qb->andWhere('r.examedAt >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (!empty($endDate)) {
            $qb->andWhere('r.examedAt <= :endDate')
                ->setParameter('endDate', date('Y/m/d/ H:i:s', strtotime('+1 day -1 second', strtotime($endDate))))
            ;
        }

        if (!empty($companyName)) {
            $qb->andWhere('c.company like :companyName')
                ->setParameter('companyName', '%'.$companyName.'%')
            ;
        }

        if ($query) {
            return $qb->getQuery();
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 给审核师查看的已退回订单列表
     */
    public function findOrderExamerBack($userId = null, $query = false, $mixed = null, $companyName = null, $startDate = null, $endDate = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.loadOfficer', 'u')
            ->leftJoin('o.company', 'c')
            ->leftJoin('o.lastBack', 'b')
            ->leftJoin('o.report', 'r')
            ->where('o.lastBack is not null and o.status = 0 and o.disable != 1')
            ->orderBy('b.createdAt', 'DESC')
        ;

        if (!empty($userId)) {
            $qb->andWhere('r.examer = :userId' )
                ->setParameter('userId', $userId)
            ;
        }

        if ($mixed) {
            $qb->andWhere('o.orderNo like :mixed or o.businessNumber like :mixed or r.vin like :mixed or r.brand like :mixed or r.series like :mixed or r.model like :mixed' )
                ->setParameter('mixed', '%'.$mixed.'%')
            ;
        }

        if ($companyName) {
            $qb->andWhere('c.company = :companyName')
                ->setParameter('companyName', $companyName)
            ;
        }

        if (!empty($startDate)) {
            $qb->andWhere('b.createdAt >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (!empty($endDate)) {
            $qb->andWhere('b.createdAt <= :endDate')
                ->setParameter('endDate', date('Y/m/d/ H:i:s', strtotime('+1 day -1 second', strtotime($endDate))))
            ;
        }

        if ($query) {
            return $qb->getQuery();
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * 获取插队的单子
     */
    public function findJump($user)
    {
        $lastDateTime = new \DateTime('19:30:00');
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and o.disable != 1 and o.locked = 0 and (o.report is null or r.hplExaming != 1) and o.jump = 1 and o.submitedAt <= :lastDateTime')
            ->setParameters(['status' => Order::STATUS_EXAM, 'lastDateTime' => $lastDateTime])
            ->orderBy('o.jumpedAt', 'ASC')
            ->setMaxResults(200)
        ;

        if ($user->getNoob() === true) {
            $qb->leftJoin('o.company', 'c')
                ->andWhere('c.noobAllowed = 0')
            ;
        }

        return $qb->getQuery()->useResultCache(true, 3)->getResult();
    }

    /**
     * 获取插队的复审单子
     */
    public function findConfirmJump()
    {
        $time = new \DateTime('19:30:00');
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.report', 'r')
            ->where('o.status = :status and o.disable = 0 and o.jump = 1 and o.submitedAt <= :time and o.locked = 0 and r.hplExaming = 0 and r.rechecker is null')
            ->setParameters(['status' => Order::STATUS_RECHECK, 'time' => $time])
            ->orderBy('o.jumpedAt', 'ASC')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取新打单
     */
    public function findNewCompanyOrder ($user)
    {
        $lastDateTime = new \DateTime('19:30:00');
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.company', 'c')
            ->where('o.status = :status and o.disable != 1 and o.locked = 0 and o.report is null and o.submitedAt <= :lastDateTime and c.companyNew = 1')
            ->setParameters(['status' => Order::STATUS_EXAM, 'lastDateTime' => $lastDateTime])
            ->orderBy('o.createdAt', 'ASC')
            ->setMaxResults(200)
        ;

        if ($user->getNoob() === true) {
            $qb->andWhere('c.noobAllowed = 0')
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取新打复审单
     */
    public function findNewCompanyConfirm ()
    {
        $time = new \DateTime('19:30:00');
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.report', 'r')
            ->leftJoin('o.company', 'c')
            ->where('o.status = :status and o.disable = 0 and o.submitedAt <= :time and o.locked = 0 and r.hplExaming = 0 and r.rechecker is null and c.companyNew = 1')
            ->setParameters(['status' => Order::STATUS_RECHECK, 'time' => $time])
            ->orderBy('o.createdAt', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取审核师自己退回的订单(先领自己的退回的，再领别人下班的)
     */
    public function findExamerSelfBack($user)
    {
        $lastDateTime = new \DateTime('19:30:00');
        $qb1 = $this->createQueryBuilder('o')
            ->leftJoin('o.lastBack', 'b')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and o.disable != 1 and o.locked = 0 and o.lastBack is not null and r.hplExaming != 1 and b.examerId = :examerId and o.submitedAt <= :lastDateTime')
            ->setParameters(['status' => Order::STATUS_EXAM, 'examerId' => $user->getId(), 'lastDateTime' => $lastDateTime])
            ->orderBy('b.createdAt', 'ASC')
            ->setMaxResults(200)
        ;

        $qb2 = $this->createQueryBuilder('o')
            ->leftJoin('o.lastBack', 'b')
            ->leftJoin('AppBundle:User', 'u', 'with', 'b.examerId = u.id')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and o.disable != 1 and o.locked = 0 and o.lastBack is not null and r.hplExaming != 1 and b.examerId != :examerId and u.isJob != 1 and o.submitedAt <= :lastDateTime')
            ->setParameters(['status' => Order::STATUS_EXAM, 'examerId' => $user->getId(), 'lastDateTime' => $lastDateTime])
            ->orderBy('b.createdAt', 'ASC')
            ->setMaxResults(200)
        ;

        if ($user->getNoob() === true) {
            $qb1->leftJoin('o.company', 'c')
                ->andWhere('c.noobAllowed = 0')
            ;

            $qb2->leftJoin('o.company', 'c')
                ->andWhere('c.noobAllowed = 0')
            ;
        }

        return array_merge($qb1->getQuery()->useResultCache(true, 3)->getResult(), $qb2->getQuery()->useResultCache(true, 3)->getResult());
    }

    /**
     * 获取自己退回的复审单(先领自己的退回的，再领别人下班的)
     */
    public function findRecheckerSelfBack($user)
    {
        $time = new \DateTime('19:30:00');
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.report', 'r')
            ->where('o.status = :status and o.disable = 0 and o.lastBack is not null and o.submitedAt <= :time and o.locked = 0 and r.hplExaming = 0 and r.rechecker = :user')
            ->setParameters(['status' => Order::STATUS_RECHECK, 'time' => $time, 'user' => $user])
            ->orderBy('r.startAt', 'ASC')
        ;

        return $qb->getQuery()->useResultCache(true, 3)->getResult();
    }

    /**
     * 获取别人下班退回的复审单
     */
    public function findRecheckerOtherBack($user)
    {
        $time = new \DateTime('19:30:00');

        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.report', 'r')
            ->leftJoin('AppBundle:User', 'u', 'with', 'r.rechecker = u.id')
            ->where('o.status = :status and o.disable = 0 and o.lastBack is not null and o.submitedAt <= :time and o.locked = 0 and r.hplExaming = 0 and r.rechecker != :user and u.isJob = 0')
            ->setParameters(['status' => Order::STATUS_RECHECK, 'time' => $time, 'user' => $user])
            ->orderBy('r.startAt', 'ASC')
        ;

        return $qb->getQuery()->useResultCache(true, 3)->getResult();
    }

    /**
     * 查找超时的单子
     */
    public function findTimeout($user)
    {
        $lastDateTime = new \DateTime('19:30:00');
        $dateTime = new \DateTime();
        $dateTime->modify('-25 minutes');

        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.lastBack', 'b')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and o.disable != 1 and o.locked = 0 and (o.report is null or r.hplExaming != 1) and (o.lastBack is null or b.examerId = :examerId) and o.submitedAt <= :dateTime and o.submitedAt <= :lastDateTime')
            ->setParameters(['status' => Order::STATUS_EXAM, 'dateTime' => $dateTime, 'examerId' => $user->getId(), 'lastDateTime' => $lastDateTime])
            ->orderBy('o.submitedAt', 'ASC')
            ->setMaxResults(200)
        ;

        if ($user->getNoob() === true) {
            $qb->leftJoin('o.company', 'c')
                ->andWhere('c.noobAllowed = 0')
            ;
        }

        return $qb->getQuery()->useResultCache(true, 3)->getResult();
    }

    /**
     * 查找超时的复审单子
     */
    public function findConfirmTimeout($user)
    {
        $lastDateTime = new \DateTime('19:30:00');
        $dateTime = new \DateTime();
        $dateTime->modify('-25 minutes');

        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.lastBack', 'b')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and o.disable = 0 and o.submitedAt <= :dateTime and o.submitedAt <= :lastDateTime and o.locked = 0 and r.rechecker is null and r.hplExaming = 0')
            ->setParameters(['status' => Order::STATUS_RECHECK, 'dateTime' => $dateTime, 'lastDateTime' => $lastDateTime])
            ->orderBy('o.submitedAt', 'ASC')
        ;

        return $qb->getQuery()->useResultCache(true, 3)->getResult();
    }

    /**
     * 查找没有超时的单子
     */
    public function findNotTimeout($user)
    {
        $lastDateTime = new \DateTime('19:30:00');
        $dateTime = new \DateTime();
        $dateTime->modify('-25 minutes');

        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.lastBack', 'b')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and o.disable != 1 and o.locked = 0 and (o.report is null or r.hplExaming != 1) and (o.lastBack is null or b.examerId = :examerId) and o.submitedAt > :dateTime and o.submitedAt <= :lastDateTime')
            ->setParameters(['status' => Order::STATUS_EXAM, 'dateTime' => $dateTime, 'examerId' => $user->getId(), 'lastDateTime' => $lastDateTime])
            ->orderBy('o.submitedAt', 'ASC')
            ->setMaxResults(200)
        ;

        if ($user->getNoob() === true) {
            $qb->leftJoin('o.company', 'c')
                ->andWhere('c.noobAllowed = 0')
            ;
        }

        return $qb->getQuery()->useResultCache(true, 3)->getResult();
    }

    /**
     * 查找没有超时的复审单子
     */
    public function findConfirmNotTimeout($user)
    {
        $lastDateTime = new \DateTime('19:30:00');
        $dateTime = new \DateTime();
        $dateTime->modify('-25 minutes');

        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.lastBack', 'b')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and o.disable = 0 and o.submitedAt > :dateTime and o.submitedAt <= :lastDateTime and o.locked = 0 and r.rechecker is null and r.hplExaming = 0 ')
            ->setParameters(['status' => Order::STATUS_RECHECK, 'dateTime' => $dateTime, 'lastDateTime' => $lastDateTime])
            ->orderBy('o.submitedAt', 'ASC')
        ;

        return $qb->getQuery()->useResultCache(true, 3)->getResult();
    }

    /**
     * 查找任务池总数(19:30前当天)
     */
    public function findAllTaskCount()
    {
        $d1 = new \DateTime('19:30:00');

        $qb = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('o.parent', 'p')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and o.disable != 1 and o.locked = 0 and (o.report is null or r.hplExaming != 1) and o.submitedAt <= :d1 and (o.parent is null or p.status = :parentStatus)')
            ->setParameters(['status' => Order::STATUS_EXAM, 'd1' => $d1, 'parentStatus' => Order::STATUS_DONE])
        ; 

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 查找复审任务池总数(19:30前当天)
     */
    public function findAllConfirmCount()
    {
        $time = new \DateTime('19:30:00');

        $qb = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->innerJoin('o.report', 'r')
            ->where('o.status = :status and o.disable != 1 and o.submitedAt <= :time and o.locked = 0 and r.hplExaming != 1 and r.rechecker is null')
            ->setParameters(['status' => Order::STATUS_RECHECK, 'time' => $time])
        ; 

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 查找退单总数(19:30前当天)
     */
    public function findAllBackCount($user)
    {
        $date = new \DateTime('19:30:00');

        $qb = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('o.parent', 'p')
            ->leftJoin('o.lastBack', 'b')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and o.disable != 1 and o.locked = 0 and o.lastBack is not null and o.submitedAt <= :date and b.examerId = :examerId and (o.parent is null or p.status = :parentStatus) and r.hplExaming = 0')
            ->setParameters(['status' => Order::STATUS_EXAM, 'date' => $date, 'examerId' => $user->getId(), 'parentStatus' => Order::STATUS_DONE])
        ; 

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 查找复审退单总数(19:30前当天)
     */
    public function findAllConfirmBackCount($user)
    {
        $time = new \DateTime('19:30:00');

        $qb = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->innerJoin('o.report', 'r')
            ->where('o.status = :status and o.disable != 1 and o.lastBack is not null and o.submitedAt <= :time and r.hplExaming != 1 and r.rechecker = :user')
            ->setParameters(['status' => Order::STATUS_RECHECK, 'time' => $time, 'user' => $user])
        ; 

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 查找退回需要审核师主管审核的单子
     */
    public function findBackAudit($mixed)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status')
            ->setParameters(['status' => Order::STATUS_CONFIRM])
            ->orderBy('o.createdAt', 'ASC')
        ;

        if (!empty($mixed)) {
            $qb->andWhere('o.orderNo like :mixed or o.businessNumber like :mixed or r.vin like :mixed or r.brand like :mixed or r.series like :mixed or r.model like :mixed' )
                ->setParameter('mixed', '%'.$mixed.'%')
            ;
        }

        return $qb->getQuery();
    }


    /**
     * 查找今天完成的任务数
     */
    public function findTodayFinishCount($user)
    {
        $dateTime = new \DateTime('00:00:00');
        $qb = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and r.examer = :user and r.examedAt >= :dateTime')
            ->setParameters(['status' => Order::STATUS_DONE, 'user' => $user, 'dateTime' => $dateTime])
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 查找今天完成的复审任务数
     */
    public function findTodayConfirmCount($user)
    {
        $time = new \DateTime('00:00:00');
        $qb = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->innerJoin('o.report', 'r')
            ->where('o.status = :status and o.disable = 0 and r.rechecker = :user and r.examedAt >= :time')
            ->setParameters(['status' => Order::STATUS_DONE, 'user' => $user, 'time' => $time])
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 今天平均完成一单的时间
     */
    public function findTodayAvg($user)
    {
        $dateTime = new \DateTime('00:00:00');
        $qb = $this->createQueryBuilder('o')
            ->select("avg(TIMESTAMPDIFF(MINUTE, r.createdAt, r.examedAt))")
            ->leftJoin('o.report', 'r')
            ->where('o.status = :status and r.examer = :user and r.examedAt >= :dateTime')
            ->setParameters(['status' => Order::STATUS_DONE, 'user' => $user, 'dateTime' => $dateTime])
        ;

        return intval($qb->getQuery()->getSingleScalarResult());
    }

    /**
     * 异常订单查询
     */
    public function findOrderAbnormal($orderNo = null, $query = false)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->leftJoin('o.loadOfficer', 'u')
            ->orderBy('r.examedAt', 'DESC')
        ;

        if ($orderNo) {
            $qb->andWhere('o.status = 2 and o.disable != 1 and o.orderNo = :orderNo' )
                ->setParameter('orderNo', $orderNo)
            ;
        } else {
            // 如果评估单号没提供的话，让查询结果为空(通过加个不存在的状态来实现)
            $qb->andWhere('o.status = 999' );
        }

        if ($query) {
            return $qb->getQuery();
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 根据各对接公司查对应公司的订单数据
     */
    public function findCompanyOrder($company = null, $orderNo = null)
    {
        $result = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->leftJoin('o.company', 'c')
            ->where('c.company = :company and o.orderNo = :orderNo')
            ->setParameters(array('company' => $company, 'orderNo' => $orderNo))
            ->orderBy('o.createdAt', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result;
    }

    /**
     * 根据report 的vin码查order, report相关信息
     */
    public function findReportsByVin($vin = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->where('o.status = 2 and r.status != 0 and o.disable != 1')
            ->orderBy('r.examedAt', 'DESC')
        ;

        if ($vin) {
            $qb->andWhere('r.vin = :vin' )
                ->setParameter('vin', $vin)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 找出可能需要发短信提醒审核师去及时审核的单子
     */
    public function findNeedSendMessageOrder()
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->where('o.status = 1 and o.disable != 1 and (o.sendMessage is null or o.sendMessage != 1) and (o.report is null or r.hplExaming != 1)')
            ->orderBy('o.submitedAt', 'ASC')
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取已完成待复检订单 status=3
     */
    public function findConfirmOrder($startDate = null, $endDate = null, $user)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->where('o.status = 3 and o.disable != 1 and r.hplExaming != 1')
            ->orderBy('o.submitedAt', 'ASC')
        ;

        if(!empty($user)) {
            $qb->andWhere('r.rechecker is null or (r.rechecker is not null and r.rechecker = :user)')
               ->setParameter('user', $user)
            ;
        } 

        if (!empty($startDate)){
            $qb->andWhere('o.submitedAt >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (!empty($endDate)){
            $qb->andWhere('o.submitedAt <= :endDate')
                ->setParameter('endDate', date('Y/m/d/ H:i:s', strtotime('+1 day -1 second', strtotime($endDate))))
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取user自己待复检订单 status=3
     */
    public function findUserConfirmOrder($user)
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.report', 'r')
            ->where('o.status = :status and o.disable = 0 and r.locked = 1 and r.hplExaming = 0 and r.rechecker = :user')
            ->setParameters(array('status' => Order::STATUS_RECHECK, 'user' => $user))
            ->orderBy('o.submitedAt', 'ASC')
            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取复检已完成订单 status=2 
     */
    public function findConfirmResultOrder($startDate = null, $endDate = null, $user)
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.report', 'r')
            ->where('o.status = 2 and o.disable != 1')
            ->orderBy('o.submitedAt', 'DESC')
        ;

        if(!empty($user)) {
            $qb->andWhere('r.rechecker = :user')
                ->setParameter('user', $user)
            ;
        } else {
            $qb->andWhere('r.rechecker is not null');
        }

        if (!empty($startDate)){
            $qb->andWhere('r.endAt >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (!empty($endDate)){
            $qb->andWhere('r.endAt <= :endDate')
                ->setParameter('endDate', date('Y/m/d/ H:i:s', strtotime('+1 day -1 second', strtotime($endDate))))
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 已复核的订单列表
     */
    public function findAllOrder($mixed = null, $id = null, $companys = null, $agencyIds = null, $query = false)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.report', 'r')
            ->leftJoin('o.company', 'c')
            ->orderBy('r.examedAt', 'DESC')
        ;

        if ($companys) {
            $qb->andWhere('c in (:companys)')
                ->setParameter('companys', $companys)
            ;
        }

        if ($agencyIds) {
            $qb->andWhere('o.agencyId in (:agencyIds)')
                ->setParameter('agencyIds', $agencyIds)
            ;
        }

        if (!empty($mixed)) {
            $qb->andWhere('o.orderNo like :mixed or o.businessNumber like :mixed or (o.report is not null and (r.vin like :mixed or r.brand like :mixed or r.series like :mixed or r.model like :mixed))')
                ->setParameter('mixed', '%'.$mixed.'%')
            ;
        }

        if ($id) {
            $qb->andWhere('o.id = :id')
                ->setParameter('id', $id)
            ;
        }

        if ($query) {
            return $qb->getQuery();
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取report价格相关信息
     */
    public function findOrderReportPrice($startDate = null, $endDate = null)
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.report', 'r')
            ->innerJoin('o.company', 'c')
            ->where("o.status =2 and r.status = 1 and o.carCity is not null and o.carCity != '' and r.year is not null and r.kilometer is not null and r.modelId is not null")
            ->orderBy('r.createdAt', 'ASC')
        ;

        if (!empty($startDate)) {
            $qb->andWhere('o.submitedAt >= :startDate')
                ->setParameter('startDate', $startDate)
            ;
        }

        if (!empty($endDate)) {
            $qb->andWhere('o.submitedAt <= :endDate')
                ->setParameter('endDate', date('Y/m/d/ H:i:s', strtotime('+1 day -1 second', strtotime($endDate))))
            ;
        }

        return $qb->getQuery();
    }
}
