<?php

namespace AppBundle\util;

use AppBundle\Traits\ContainerAwareTrait;

class RabbitMQ
{
    use ContainerAwareTrait;

    public function sendPianganImgUploadNotify($orderId)
    {
        $msg = [
            "orderId" => $orderId
        ];
        $this->send("pingan_img_upload_sender", $msg);
    }

    public function sendCompanyNotify($orderNo, $times = 0)
    {
        $msg = [
            "order_no" => $orderNo,
            "times" => $times
        ];
        $delay = 0;
        // 延迟1分钟
        if ($times > 0) {
            $delay = 60 * 1000;
        }

        $this->send("company_sender", $msg, $delay);
    }

    public function sendJpush($msg){
        $this->send("jpush_sender", $msg);
    }

    public function send($producerName, $msg, $delay = 0)
    {
        $rabbitmq_enable = $this->getParameter("rabbitmq_enable");
        if (empty($rabbitmq_enable)) {
            return;
        }

        $this->get("old_sound_rabbit_mq.{$producerName}_producer")->publish(serialize($msg), "", [], ["x-delay"=> $delay]);
    }
}
