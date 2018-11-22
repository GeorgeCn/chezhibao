<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\User;
use AppBundle\Event\HplEvents;
use AppBundle\Event\OrderSubmitEvent;
use AppBundle\Traits\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Entity\OrderPicture;
use \DateTime;
use AppBundle\Entity\Order;

class OrderSubmitSubscriber implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        return [
            HplEvents::ORDER_SUBMIT => 'onOrderSubmit'
        ];
    }

    public function onOrderSubmit(OrderSubmitEvent $event)
    {

        $this->savePhotoRecord($event);
        $this->updateOrderAddress($event);

        $this->handleRecheckOrder($event);
    }

    /**
     * 订单提交时，图片操作记录添加
     * @param OrderSubmitEvent $event
     */
    private function savePhotoRecord(OrderSubmitEvent $event)
    {
        $order = $event->getOrder();
        if(!isset($order->photorecord) || !($order->photorecord)){
            return ;
        }
        $photorecord = $order->photorecord;
        $photorecord = json_decode($photorecord, true);
        if(!is_array($photorecord)){
            return ;
        }

        $em = $this->getDoctrineManager();
        $loadOfficer = $order->getLoadOfficer();
        $company = $loadOfficer->getAgencyRels()[0]->getCompany()->getId();
        $orderLogic = $this->get("OrderLogic");
        $pictures = $orderLogic->getPicturesKeyMetaDatas($company);

        foreach ($photorecord as $key => $values) {
            foreach ($values as $value) {
                $this->addOrderPicture($em, $pictures, $order, $key, $value);
            }
        }
        return ;
    }

    public function addOrderPicture($em, $pictures, $order, $key, $photorecord)
    {
        $orderPhotoTime = isset($photorecord['photo_time']) ? $photorecord['photo_time'] : 0;
        $rephotographTimes = isset($photorecord['rephotograph_times']) ? $photorecord['rephotograph_times'] : 0;
        $verifyTimes = isset($photorecord['verify_times']) ? $photorecord['verify_times'] : 0;
        $origin = isset($photorecord['origin']) ? $photorecord['origin'] : '';
        $longitude = isset($photorecord['longitude']) ? $photorecord['longitude'] : '';
        $latitude = isset($photorecord['latitude']) ? $photorecord['latitude'] : '';
        $createdAt = new DateTime();
        $pictureAt = new DateTime();
        $pictureAt->setTimestamp($orderPhotoTime);
        $pictureName = isset($pictures[$key]) ? $pictures[$key]['display'] : '';
        $pictureOrigin = isset($photorecord['origin']) ? $photorecord['origin'] : '';
        $longitude = isset($photorecord['longitude']) ? $photorecord['longitude'] : '';
        $latitude = isset($photorecord['latitude']) ? $photorecord['latitude'] : '';

        $loadOfficer = $order->getLoadOfficer();
        $orderPicture = new OrderPicture();
        $orderPicture->setLoadOfficer($loadOfficer)
                        ->setOrder($order)
                        ->setCreatedAt($createdAt)
                        ->setPictureAt($pictureAt)
                        ->setPictureKey($key)
                        ->setPictureName($pictureName)
                        ->setPictureOrigin($pictureOrigin)
                        ->setRephotographTimes($rephotographTimes)
                        ->setVerifyTimes($verifyTimes)
                        ->setLongitude($longitude)
                        ->setLatitude($latitude);
        $em->persist($orderPicture);
        $em->flush();
    }

    public function updateOrderAddress(OrderSubmitEvent $event)
    {
        $re = '';
        $order = $event->getOrder();
        if(isset($order->add_order_address) && $order->add_order_address){
            $re = $this->get('OrderLogic')->updateOrderAddress($order);
            return $re;
        }
        return $re;
    }

    public function handleRecheckOrder(OrderSubmitEvent $event)
    {
        $order = $event->getOrder();
        $report = $order->getReport();
        if ($report && $report->getRechecker()) {
            $order->setStatus(Order::STATUS_RECHECK);
            $em = $this->getDoctrineManager();
            $em->flush();
        }
    }
}
