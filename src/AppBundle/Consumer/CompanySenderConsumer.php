<?php
namespace AppBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use AppBundle\Traits\ContainerAwareTrait;
use \Exception;
use AppBundle\Entity\Order;

class CompanySenderConsumer extends ContainerAware implements ConsumerInterface
{
    use ContainerAwareTrait;

    public function execute(AMQPMessage $msg)
    {
        // 防止每次修改数据库后，都需要重启consumer
        $em = $this->getDoctrine()->getManager();
        $em->clear(); // Detaches all objects from Doctrine!

        $msg = unserialize($msg->body);
        $orderNo = $msg["order_no"];
        if(empty($orderNo)) {
            echo date('Y-m-d H:i:s').": $orderNo is null! $msg \n";
            return ConsumerInterface::MSG_ACK;
        }

        $order = $this->getRepo('AppBundle:Order')->findOneByOrderNo($orderNo);
        if (empty($order)) {
            echo date('Y-m-d H:i:s').": $orderNo doesn't exist $msg\n";
            return ConsumerInterface::MSG_ACK;
        }

        $times = $msg["times"];
        $noticeResult = $this->container->get('app.third.notify_company')->noticeCompany($orderNo);

        $order->setNotifiedAt(new \DateTime());
        $order->setNotifiedTimes($order->getNotifiedTimes() + 1);

        if (false === $noticeResult) {
            $content = date('Y-m-d H:i:s').": $orderNo notified failed!\n";
            echo $content;

            if ($times >= 2) {
                $order->setNotifiedStatus(Order::NOTIFIED_STATUS_FAILED);
                $em->flush();
                echo "has retry more than 3 times! \n";

                return ConsumerInterface::MSG_ACK;
            }

            $em->flush();
            $this->get("util.rabbitmq")->sendCompanyNotify($orderNo, $times + 1);

            return ConsumerInterface::MSG_ACK;
        } else {
            $order->setNotifiedStatus(Order::NOTIFIED_STATUS_SUCCESS);
            $em->flush();
            echo date('Y-m-d H:i:s').": $orderNo has been notified successfully!\n";

            return ConsumerInterface::MSG_ACK;
        }
    }
}