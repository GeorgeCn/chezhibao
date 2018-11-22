<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\Order;
use AppBundle\Event\HplEvents;
use AppBundle\Event\OrderPrimaryExamEvent;
use AppBundle\Traits\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderPrimaryExamSubscriber implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        return [
            HplEvents::ORDER_PRIMARY_EXAM => 'onOrderPrimaryExam',
        ];
    }

    public function OnOrderPrimaryExam(OrderPrimaryExamEvent $event)
    {
        $report = $event->getReport();
        $report->setLocked(false);
        $report->setExamedAt(new \DateTime());
        $order = $this->getRepo('AppBundle:Report')->findOrder($report->getId());
        $order->setStatus(Order::STATUS_RECHECK);
        $order->setLocked(false);
        $order->setLockOwner(null);

        $this->getDoctrineManager()->flush();
    }
}
