<?php

namespace AppBundle\Business;

use AppBundle\Traits\ContainerAwareTrait;
use YYC\FoundationBundle\Entity\Maintain;

/**
 * 维保记录
 */
class MaintainLogic
{
    use ContainerAwareTrait;

    public function getMaintainData($report)
    {
        $result = [];
        $id = $report->getMaintain();

        if (!$id) {
            return $result;
        }

        $maintain = $this->get('doctrine')
            ->getRepository('YYCFoundationBundle:Maintain')
            ->find($id)
        ;
// dump($maintain);exit;
        if (!$maintain) {
            return $result;
        }

        //获取维保来源 (大圣来了，车鉴定 ，查博士)
        $source = $maintain->getSupplierType();
        switch ($source) {
            case Maintain::TYPE_DSLL:
                $result = $this->getDsllData($maintain);
                break;

            case Maintain::TYPE_CJD:
                $result = $this->getCjdData($maintain);
                break;

            case Maintain::TYPE_CBS:
                $result = $this->getCbsData($maintain);
                break;

            case Maintain::TYPE_JUHE:
                $result = $this->getCbsData($maintain);
                break;

            case Maintain::TYPE_ANTQUEEN:
                $result = $this->getAntQueenData($maintain);
                break;

            default:
                $result = $this->getDsllData($maintain);
                break;
        }

        return $result;
    }

    /**
     * 获取大圣来了数据
     */
    public function getDsllData($maintain)
    {
        $result = [];
        $data = $maintain->getResults();
        if (!$data) {
            return $result;
        }

        if (isset($data['result_content'])) {
            $tmp = json_decode($data['result_content'], true);
            foreach ($tmp as $v) {
                $t['date'] = $v['date'];
                $t['kilometers'] = $v['kilometers'];
                $t['content'] = $v['content'];
                $result[] = $t;
            }
        }

        return $result;
    }

    /**
     * 获取车鉴定数据
     */
    public function getCjdData($maintain)
    {
        $result = [];
        $data = $maintain->getResults();

        if (!$data) {
            return $result;
        }

        if (isset($data['reportJson'])) {
            $tmp = $data['reportJson'];
            foreach ($tmp as $v) {
                $t['date'] = $v['repairDate'];
                $t['kilometers'] = $v['mileage'];
                $t['content'] = $v['content'];
                $result[] = $t;
            }
        }

        return $result;
    }

    /**
     * 获取查博士数据
     */
    public function getCbsData($maintain)
    {
        $result = [];
        $data = $maintain->getResults();

        if (!$data) {
            return $result;
        }

        if (isset($data['normalRepairRecords'])) {
            $tmp = $data['normalRepairRecords'];
            foreach ($tmp as $v) {
                $t['date'] = $v['date'];
                $t['kilometers'] = $v['mileage'];
                $t['content'] = $v['content'];
                $result[] = $t;
            }
        }

        return $result;
    }

    /**
     * 获取聚合数据
     */
    public function getJuheData($maintain)
    {
        $result = [];
        $data = $maintain->getResults();

        if (!$data) {
            return $result;
        }

        if (isset($data['result_content'])) {
            $tmp = $data['result_content'];
            foreach ($tmp as $v) {
                $t['date'] = $v['date'];
                $t['kilometers'] = $v['mileage'];
                $t['content'] = $v['content'];
                $result[] = $t;
            }
        }

        return $result;
    }

    /**
     * 获取蚂蚁女王数据
     */
    public function getAntQueenData($maintain)
    {
        $result = [];
        $data = $maintain->getResults();

        if (!$data) {
            return $result;
        }

        if (isset($data['query_text'])) {
            $tmp = json_decode($data['query_text'], true);
            foreach ($tmp as $v) {
                $t['date'] = $v['date'];
                $t['kilometers'] = $v['kilm'];
                $t['content'] = $v['detail'];
                $result[] = $t;
            }
        }

        return $result;
    }
}