<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Event\OrderFinishEvent;
use AppBundle\Event\HplEvents;
use AppBundle\Traits\ContainerAwareTrait;
use AppBundle\Entity\Report;
use AppBundle\Entity\User;

class OrderFinishSubscriber implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        return [
            HplEvents::ORDER_FINISH => 'onOrderFinish'
        ];
    }

    public function onOrderFinish(OrderFinishEvent $event)
    {
        $report = $event->getReport();
        $reportId = $report->getId();
        $order = $this->getRepo('AppBundle:Report')->findOrder($reportId);

        // 解锁逻辑
        $em = $this->getDoctrineManager();
        $order->setLocked(false);
        $order->setLockOwner(null);
        $em->flush();

        //获取更新的$newreport
        $newreport = $order->getReport();
        $this->sendOrderJpush($newreport, $order);
        $this->sendOrderSm($newreport, $order);

    }

    private function sendOrderJpush($report, $order)
    {
        // 临时C端用户，屏蔽推送
        $userType = $order->getLoadOfficer()->getType();
        if ($userType == User::TYPE_TEMP) {
            return;
        }

        $jpushDebug = $this->getParameter('jpush_debug');
        //使用youyiche项目的jpush推送通道，拓展的规则
        $userName = $order->getLoadOfficer()->getUsername();
        $orderNo = $order->getOrderNo();
        $reportStatus = $report->getStatus();
        $reportarr = $report->getReport();
        $users[] = $userName;

        $pictures = $order->getPictures();
        $orderLogic = $this->get("OrderLogic");
        $picture = $orderLogic->getMainPicture($pictures);

        $msg = [];
        $msg['custom_tag'] = 'hpl_jpush';
        //$jpushDebug true 发送给用户  false:测试发送给管理人员
        $msg['user_names'] = $jpushDebug ? $users : ["admin"];
        $msg['extras']['order_id'] = $order->getId();
        $msg['extras']['order_status'] = $order->getStatus();
        $msg['extras']['report_status'] = $report->getStatus();
        $msg['extras']['type'] = 'order_finish';
        $msg['extras']['picture'] = $picture;

        $msg['alert'] = '';

        //新 ToC app jpush 推送，考虑原app 还有使用的 用户公司和extraData 结合判断
        $company = $order->getCompany()->getCompany();
        $toc = ['客服创建'];
        if(in_array($company, $toc)){
            $msg['custom_tag'] = 'jiance_jpush';
        }

        if ($reportStatus == Report::STATUS_PASS) {
            $msg['alert'] = '车辆审核通过：您的订单【' . $orderNo . '】已通过审核';//，评估价格'.$price.'元';
        } else if ($reportStatus == Report::STATUS_REFUSE) {
            $msg['alert'] = '车辆审核拒绝：您的订单【' . $orderNo . '】审核未通过';
        }
        if ($msg['alert']) {
            $utilrabbitmq = $this->get("util.rabbitmq");
            $utilrabbitmq->sendJpush($msg);
        }
    }

    private function sendOrderSm($report, $order)
    {
        // 临时C端用户，屏蔽推送
        $userType = $order->getLoadOfficer()->getType();
        if ($userType == User::TYPE_TEMP) {
            return;
        }

        $orderNo = $order->getOrderNo();
        $loadOfficer = $order->getLoadOfficer();

        //是否接收短信
        if (!$loadOfficer->getReceiveOwner()) {
            return;
        }

        $mobile = $loadOfficer->getMobile();
        $reportStatus = $report->getStatus();
        $template = '';
        if ($reportStatus == Report::STATUS_PASS) {
            $template = 'yjc_examer_pass';
        } else if ($reportStatus == Report::STATUS_REFUSE) {
            $template = 'yjc_examer_refuse';
        }
        if ($template) {
            $this->get('app.third.sms')->send($template, $mobile, [$orderNo]);
        }
    }
}
