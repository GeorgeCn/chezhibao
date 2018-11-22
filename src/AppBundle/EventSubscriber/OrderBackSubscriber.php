<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Event\OrderBackEvent;
use AppBundle\Event\HplEvents;
use AppBundle\Traits\ContainerAwareTrait;

class OrderBackSubscriber implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        return [
            HplEvents::ORDER_BACK => 'onOrderBack'
        ];
    }

    public function onOrderBack(OrderBackEvent $event)
    {
        $report = $event->getReport();

        //退回的单子自动清空创建时间，方便后续计算每单的平均时间
        $report->setCreatedAt(null);
        $em = $this->getDoctrineManager();
        $em->flush();

        $reportId = $report->getId();
        $order = $this->getRepo('AppBundle:Report')->findOrder($reportId);
        $this->sendOrderJpush($report, $order);
        $this->sendOrderSm($order);
    }

    private function sendOrderJpush($report, $order)
    {
        $jpushDebug = $this->getParameter('jpush_debug');
        //使用youyiche项目的jpush推送通道，拓展的规则
        $userName = $order->getLoadOfficer()->getUsername();
        $orderNo = $order->getOrderNo();
        $users[] = $userName;

        $pictures = $order->getPictures();
        $orderLogic = $this->get("OrderLogic");
        $picture = $orderLogic->getMainPicture($pictures);

        $msg = [];
        $msg['custom_tag'] = 'hpl_jpush';
        //$jpushDebug true 发送给用户  false:测试发送给管理人员
        $msg['user_names'] = $jpushDebug ? $users : ["admin"];
        $msg['alert'] = '车辆订单退回：您的订单【'.$orderNo.'】被审核人员退回，点击查看详情并完善车辆信息。';
        $msg['extras']['order_id'] = $order->getId();
        $msg['extras']['order_status'] = $order->getStatus();
        $msg['extras']['type'] = 'order_back';
        $msg['extras']['picture'] = $picture;
        $msg['companyId'] = $order->getCompany() ? $order->getCompany()->getId() : 0;

        //新 ToC app jpush 推送，考虑原app 还有使用的 用户公司和extraData 结合判断
        $company = $order->getLoadOfficer()->getAgencyRels()[0]->getCompany()->getCompany();
        $toc = ['客服创建'];
        if(in_array($company, $toc)){
            $msg['custom_tag'] = 'jiance_jpush';
        }

        $utilrabbitmq = $this->get("util.rabbitmq");
        $utilrabbitmq->sendJpush($msg);
    }

    private function sendOrderSm($order)
    {
        $orderNo = $order->getOrderNo();
        $loadOfficer = $order->getLoadOfficer();
        $mobile = $loadOfficer->getMobile();

        $this->get('app.third.sms')->send('yjc_examer_back', $mobile, [$orderNo]);
    }
}
