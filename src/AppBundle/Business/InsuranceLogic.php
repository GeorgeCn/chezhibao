<?php

namespace AppBundle\Business;

use AppBundle\Traits\ContainerAwareTrait;
use YYC\FoundationBundle\Entity\Insurance;

/**
 * 保险记录
 */
class InsuranceLogic
{
    use ContainerAwareTrait;

    public function getInsuranceData($order)
    {
        $result = [];
        $id = $order->getInsuranceId();

        if (!$id) {
            return $result;
        }

        $insurance = $this->get('doctrine')
            ->getRepository('YYCFoundationBundle:Insurance')
            ->find($id)
        ;

        if (!$insurance) {
            return $result;
        }

        //获取保险来源 (老司机)
        $source = $insurance->getSupplierType();
        switch ($source) {
            case Insurance::TYPE_LSJ:
                $result = $this->getLsjData($insurance);
                break;

            default:
                break;
        }

        return $result;
    }

    /**
     * 获取老司机数据
     */
    public function getLsjData($insurance)
    {
        $result = [];
        $data = $insurance->getResults();
        if (!$data) {
            return $result;
        }

        if (isset($data['Claims'])) {
            $tmp = json_decode($data['Claims'], true);
            foreach ($tmp as $v) {
                $t['date'] = $v['ClaimDate'];
                $t['totalFee'] = $v['TotalFee'];
                $t['description'] = $v['Description'];
                $t['detail'] = $v['RepairDetail'];
                $result[] = $t;
            }
        }

        return $result;
    }
}