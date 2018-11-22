<?php

namespace AppBundle\Business;

use AppBundle\Entity\Agency;
use AppBundle\Entity\AgencyRel;
use AppBundle\Entity\Config;
use AppBundle\Traits\ContainerAwareTrait;

class ApplyLogic
{
    use ContainerAwareTrait;

    public function getAgencyInfoFromApply ($agencyID = null)
    {
        $agencyInfo = array();
        if(empty($agencyID)) return $agencyInfo;

        $em = $this->getDoctrine()->getManager();
        $agency = $em->getRepository("AppBundle:Agency")->find($agencyID);

        if (!empty($agency)) {
            $agencyInfo['company'] = $agency->getCompany()->getCompany();
            $agencyInfo['agency'] = $agency->getName();
            return $agencyInfo;
        } else {
            return $agencyInfo;
        }
    }

    public function findUserCompines ()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $agencyRel = $em->getRepository("AppBundle:AgencyRel")->createQueryBuilder('ar')
            ->leftJoin('ar.company', 'c')
            ->Where('ar.user = :user and ar.grade = 2' )
            ->setParameter('user', $user)
            ->select(['c.id']);
        return $agencyRel->getQuery()->getResult();
    }

    public function findUserAgency ($company)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $agencyRel = $em->getRepository("AppBundle:AgencyRel")->createQueryBuilder('ar')
            ->leftJoin('ar.agency', 'a')
            ->Where('ar.user = :user and ar.company = :company' )
            ->setParameter('user', $user)
            ->setParameter('company', $company)
            ->select(['a.id']);
        return $agencyRel->getQuery()->getResult();
    }
}
