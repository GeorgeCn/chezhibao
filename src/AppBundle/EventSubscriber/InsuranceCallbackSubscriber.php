<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use YYC\FoundationBundle\YYCFoundationEvents;
use YYC\FoundationBundle\Event\InsuranceCallbackEvent;
use AppBundle\Traits\ContainerAwareTrait;

class InsuranceCallbackSubscriber implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        return array(
            YYCFoundationEvents::INSURANCE_CALLBACK => 'onInsuranceCallback'
        );
    }

    public function onInsuranceCallback(InsuranceCallbackEvent $event)
    {
        $orderNo = $event->getOrderNo();
        $insuranceId = $event->getInsuranceId();

        $em = $this->getDoctrine()->getManager();
        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->findOneBy(array('orderNo' => $orderNo));

        if ($order) {
            $order->setInsuranceId($insuranceId);

            $em->persist($order);
            $em->flush();
        }
    }
}
