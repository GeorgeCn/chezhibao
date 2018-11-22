<?php

namespace YYC\FoundationBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * 当保险公司回调我们时的事件
 */
class InsuranceCallbackEvent extends Event
{
    private $orderNo;
    private $insuranceId;

    public function __construct($orderNo, $insuranceId)
    {
        $this->orderNo = $orderNo;
        $this->insuranceId = $insuranceId;
    }

    /**
     * 获取远程检测评估单号
     */
    public function getOrderNo()
    {
        return $this->orderNo;
    }

    /**
     * 获取保险记录的id
     */
    public function getInsuranceId()
    {
        return $this->insuranceId;
    }
}
