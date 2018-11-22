<?php

namespace AppBundle\Business;

use AppBundle\Entity\Order;
use AppBundle\Traits\ContainerAwareTrait;
use AppBundle\Entity\Report;
use AppBundle\Model\MetadataManager;
use \DateTime;

class BusinessReportLogic
{
    use ContainerAwareTrait;
    /**
     * 信贷员业绩,并判断用户的级别
     */
    public function loadOfficerQuery($user, $dt_start = "", $dt_end = "", $company = '', $agency = '')
    {
        $em = $this->getDoctrineManager();
        $qb = $em->getRepository('AppBundle:Order')
                    ->createQueryBuilder('o')
                    ->leftJoin('o.loadOfficer', 'u')
                    ->leftJoin('o.company', 'c')
                    ->leftJoin('o.report','r')
                    ->where('o.status != 0 and o.disable != 1')
                    ->orderBy('o.submitedAt','DESC');

        if (!empty($dt_start)) {
            $qb->andWhere('o.submitedAt >= :dt_start' )
                ->setParameter('dt_start', $dt_start);
        }
        if (!empty($dt_end)) {
            $timeregdateto = strtotime($dt_end)+86400-1;
            $regdateto_datetime = new DateTime('@'.$timeregdateto);
            $regdateto_datetime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            $qb->andWhere('o.submitedAt <= :dt_end' )
                ->setParameter('dt_end', $dt_end);
        }

        $result =  $this->selectOrders($user, $qb, $company, $agency);
        return $result;
    }

    private function selectOrders($user, $qb, $company = '', $agency = ''){
        $roles = [
            "ROLE_LOADOFFICER",
            "ROLE_LOADOFFICER_MANAGER",
            "ROLE_EXAMER_HPL",
            "ROLE_ADMIN_HPL",
            "ROLE_EXAMER",
            "ROLE_ADMIN",
            "ROLE_SUPER_ADMIN",
            "ROLE_EXAMER_MANAGER",
        ];
        $selectRole = '';
        foreach($roles as $role){
            if($user->hasRole($role)){
                $selectRole = $role;
            }
        }
        $result = [];
        switch ($selectRole) {
            case 'ROLE_LOADOFFICER':
                $result = $this->handleResult($user, $qb, $company, $agency);
                break;

            case 'ROLE_LOADOFFICER_MANAGER':
                $agencyRels = $user->getAgencyRels();
                foreach ($agencyRels as $agencyRel) {
                    $ownCompany = $agencyRel->getCompany()->getCompany();
                    $ownAgency = $agencyRel->getAgency()->getName();
                    $ret = $this->handleResult(null, $qb, $company ?: $ownCompany, $agency ?: $ownAgency);
                    $result = array_merge($result, $ret);
                }
                break;

            case 'ROLE_EXAMER_HPL':
            case 'ROLE_ADMIN_HPL':
                $agencyRel = $user->getAgencyRels()[0];
                $ownCompany = $agencyRel->getCompany()->getCompany();
                $result = $this->handleResult(null, $qb, $company ?: $ownCompany);
                break;

            case 'ROLE_EXAMER':
            case 'ROLE_ADMIN':
            case 'ROLE_SUPER_ADMIN':
            case 'ROLE_EXAMER_MANAGER':
                $result = $this->handleResult(null, $qb, $company, $agency);
                break;

            default:
                break;
        }
        return $result;
    }

    public function handleResult($user = null, $qb, $company = '', $agency = '')
    {
        $em = $this->getDoctrineManager();
        $qbOrder = $em->getRepository('AppBundle:Order')->createQueryBuilder('o')
            ->where('o.loadOfficer is not null')
            ->leftJoin('o.company', 'c')
            ->groupBy('o.loadOfficer', 'o.company')
        ;

        if ($user) {
            $qbOrder->andWhere('o.loadOfficer = :user')
                ->setParameter('user', $user)
            ;
        }

        if ($company) {
            $qbOrder->andWhere('c.company = :company')
                ->setParameter('company', $company)
            ;
        }

        if ($agency) {
            $qbOrder->andWhere('o.agencyName = :agencyName')
                ->setParameter('agencyName', $agency)
            ;
        }

        $resultOrder = $qbOrder
            ->getQuery()
            ->getResult()
        ;

        $result = [];
        foreach($resultOrder as $order){
            $result[] = $this->selectOrderByrole($order->getLoadOfficer(), $qb, $order->getCompany() ? $order->getCompany()->getCompany() : '' , $order->getAgencyName());
        }

        return $result;
    }

    private function selectOrderByrole($user, $qb, $company = '', $agency = ''){
        if ($company) {
            $qb->andWhere('c.company = :company')
                ->setParameter('company', $company)
            ;
        }

        $result = [];
        $qbAll = clone $qb;
        $qbPass = clone $qb;
        $qbRefused = clone $qb;
        $qbBack = clone $qb;

        $resultAll = $qbAll->select('COUNT(o.id) AS rnum')
            ->andWhere('o.loadOfficer = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleResult()
        ;
        $resultPass = $qbPass->select('COUNT(r.id) AS rnum')
            ->andWhere('o.loadOfficer = :user')
            ->setParameter('user', $user)
            ->andWhere('r.id is not null')
            ->andWhere('r.status = 1' )
            ->getQuery()
            ->getSingleResult()
        ;
        $resultRefused = $qbRefused->select('COUNT(r.id) AS rnum')
            ->andWhere('o.loadOfficer = :user')
            ->setParameter('user', $user)
            ->andWhere('r.id is not null' )
            ->andWhere('r.status = 2' )
            ->getQuery()
            ->getSingleResult()
        ;

        $result['company'] = $company;
        $result['agencyName'] = $agency;
        $result['name'] = $user->getName();
        $result['resultall'] = (int)$resultAll['rnum'];
        $result['resultpass'] = (int)$resultPass['rnum'];
        $result['resultrefused'] = (int)$resultRefused['rnum'];;
        $result['resultaction'] = $result['resultall'] - $result['resultpass'] - $result['resultrefused'];

        return $result;
    }

    /**
     * 车辆祥表
     */
    public function vehicleQuery($user, $mixed = "", $status = "",$dt_start = "",$dt_end = "", $company = '', $agency = '')
    {
        $em = $this->getDoctrineManager();
        $qb = $em->getRepository('AppBundle:Order')
                    ->createQueryBuilder('o')
                    ->where('o.status != 0 and o.disable != 1')
                    ->leftJoin('o.report','r')
                    ->leftJoin('o.company','c')
                    ->andWhere("o.report is not null and r.status != 0 and r.createdAt is not null and r.examedAt is not null" )
                    ->orderBy('o.submitedAt','DESC')
        ;
        if (!empty($mixed)) {
            $qb->andWhere('o.orderNo like :mixed or o.businessNumber like :mixed or r.vin like :mixed or r.brand like :mixed or r.series like :mixed or r.model like :mixed' )
                ->setParameter('mixed', '%'.$mixed.'%')
            ;
        }
        if (!empty($status) && $status != 3) {
            $qb->andWhere('r.status = :status')
                ->setParameter('status', $status)
            ;
        }
        if (!empty($dt_start)) {
            $qb->andWhere('o.submitedAt >= :dt_start')
                ->setParameter('dt_start', $dt_start)
            ;
        }
        if (!empty($dt_end)) {
            $qb->andWhere('o.submitedAt <= :dt_end')
                ->setParameter('dt_end', date('Y/m/d/ H:i:s', strtotime('+1 day -1 second', strtotime($dt_end))))
            ;
        }

        if (!empty($company)) {
            $qb->andWhere('c.company = :companyName')
                ->setParameter('companyName', $company)
            ;
        }

        if (!empty($agency)) {
            $qb->andWhere('o.agencyName = :agency')
                ->setParameter('agency', $agency)
            ;
        }

        $newQb = $this->selectVehicle($user, $qb);

        return $newQb->getQuery();
    }

    private function selectVehicle($user, $qb){
        $roles = [
            "ROLE_LOADOFFICER",
            "ROLE_LOADOFFICER_MANAGER",
            "ROLE_EXAMER_HPL",
            "ROLE_ADMIN_HPL",
            "ROLE_EXAMER",
            "ROLE_EXAMER_MANAGER",
            "ROLE_ADMIN",
            "ROLE_SUPER_ADMIN",
        ];
        $selectRole = '';
        foreach($roles as $role){
            if($user->hasRole($role)){
                $selectRole = $role;
            }
        }

        switch ($selectRole) {
            case 'ROLE_LOADOFFICER':
                $qb->andWhere('o.loadOfficer = :user')
                    ->setParameter('user', $user)
                ;
                break;

            case 'ROLE_LOADOFFICER_MANAGER':
                $agencyNames = [];
                foreach ($user->getAgencyRels() as $agencyRel) {
                    $agency = $agencyRel->getAgency();
                    $agencyNames[] = $agency->getName();
                }

                $qb->andWhere('o.agencyName in (:agencyNames)')
                    ->setParameter('agencyNames', $agencyNames)
                ;
                break;

            case 'ROLE_EXAMER_HPL':
            case 'ROLE_ADMIN_HPL':
                $qb->andWhere('o.company = :company')
                    ->setParameter('company', $user->getAgencyRels()[0]->getCompany())
                ;
                break;

            case 'ROLE_EXAMER':
            case 'ROLE_EXAMER_MANAGER':
            case 'ROLE_ADMIN':
            case 'ROLE_SUPER_ADMIN':
                break;

            default:
                break;
        }

        return $qb;
    }
}
