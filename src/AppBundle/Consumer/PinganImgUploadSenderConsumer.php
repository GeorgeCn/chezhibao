<?php

namespace AppBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class PinganImgUploadSenderConsumer extends ContainerAware implements ConsumerInterface
{
    public function execute(AMQPMessage $msg)
    {
        $msg = unserialize($msg->body);
        $orderId = $msg["orderId"];

        // 处理平安上传图片
        $uploadResult = $this->container->get('ReportLogic')->handlePinganPicture($orderId);

        if (false === $uploadResult) {
            echo date('Y-m-d H:i:s').": upload imgs of pingan orderId: $orderId failed!";
        } else {
            echo date('Y-m-d H:i:s').": upload imgs of pingan orderId: $orderId successfully!";
        }

        $order = $this->container->get('doctrine')->getRepository('AppBundle:Order')->find($orderId);
        $report = $order->getReport();
        // 发送通知平安的message到队列
        $this->container->get('util.rabbitmq')->sendCompanyNotify($order->getOrderNo());

        return ConsumerInterface::MSG_ACK;
    }
}