<?php

namespace AppBundle\Business;

use AppBundle\Entity\Config;
use AppBundle\Entity\Order;
use AppBundle\Entity\Report;
use AppBundle\Event\HplEvents;
use AppBundle\Event\OrderSubmitEvent;
use AppBundle\Traits\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GetOrderLogic
{
    use ContainerAwareTrait;

    // 获取user当前领到的订单
    public function getLockedOrder($user)
    {
        return $this->getRepo("AppBundle:Order")->findOneBy(["locked" => true, "lockOwner" => $user, "status" => Order::STATUS_EXAM]);
    }

    // 获取user当前领到的复审订单
    public function getConfirmOrder($user)
    {
        return $this->getRepo("AppBundle:Order")->findOneBy(["locked" => true, "lockOwner" => $user, "status" => Order::STATUS_RECHECK]);
    }

    // 返回 code, order
    // code = 0 领到单子，order返回
    // code = 1 暂时没单子
    // code = 2 今天单子领完了
    public function getOrder($user){
        // 先找已经领到的
        $order = $this->getLockedOrder($user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }

        // 你搜索状态考虑清楚，order的状态，report的状态，lock的状态，时间顺序是asc，等等
        // 优先级A 先找插队的
        $orders = $this->getRepo("AppBundle:Order")->findJump($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }
        //新增优先级 B+ 新打单
        $orders = $this->getRepo("AppBundle:Order")->findNewCompanyOrder($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        } 
        // 优先级B 找自己退回的
        $orders = $this->getRepo("AppBundle:Order")->findExamerSelfBack($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }
        // 优先级C D 的逻辑
        $abnormal = $user->getAbnormal();
        $orders = $abnormal ? $this->getRepo("AppBundle:Order")->findTimeout($user) : $this->getRepo("AppBundle:Order")->findNotTimeout($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }
        $orders = $abnormal ? $this->getRepo("AppBundle:Order")->findNotTimeout($user) : $this->getRepo("AppBundle:Order")->findTimeout($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }

        $d1 = new \DateTime('19:30:00');
        $d2 = new \DateTime();
        if ($d2 >= $d1 ) {
            return ["code"=>2, "order"=>null];
        }

        return ["code"=>1, "order"=>null];
    }

    // 复审接单返回 code, order
    // code = 0 领到单子，order返回
    // code = 1 暂时没单子
    // code = 2 今天单子领完了
    public function getConfirm ($user)
    {
        // 先找已经领到的
        $order = $this->getConfirmOrder($user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }

        // 你搜索状态考虑清楚，order的状态，report的状态，lock的状态，时间顺序是asc，等等
        // 优先级A 先找插队的
        $orders = $this->getRepo("AppBundle:Order")->findConfirmJump();
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }
        //新增优先级 B+ 新打单
        $orders = $this->getRepo("AppBundle:Order")->findNewCompanyConfirm($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        } 
        // 优先级B 找自己退回的,再找别人下班的
        $orders = $this->getRepo("AppBundle:Order")->findRecheckerSelfBack($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }
        $orders = $this->getRepo("AppBundle:Order")->findRecheckerOtherBack($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }
        // 优先级C 处理未超时单
        $orders = $this->getRepo("AppBundle:Order")->findConfirmNotTimeout($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }
        // 优先级D 处理超时单
        $orders = $this->getRepo("AppBundle:Order")->findConfirmTimeout($user);
        $order = $this->lockOrder($orders, $user);
        if (!empty($order)) {
            return ["code"=>0, "order"=>$order];
        }

        $d1 = new \DateTime('19:30:00');
        $d2 = new \DateTime();
        if ($d2 >= $d1 ) {
            return ["code"=>2, "order"=>null];
        }

        return ["code"=>1, "order"=>null];
    }
    
    private function lockOrder($orders, $user)
    {
        foreach ($orders as $order) {
            $parent = $order->getParent();
            if ($parent && $parent->getStatus() !== Order::STATUS_DONE) {
                continue;
            }

            $em = $this->getDoctrineManager();
            $em->getConnection()->beginTransaction();
            try {
                $em->lock($order, \Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
                $em->refresh($order);
                if ($order->getLocked()) {
                    $em->getConnection()->commit();
                    continue;
                }
                $order->setLocked(true);
                $order->setLockOwner($user);
                $em->flush();
                $em->getConnection()->commit();
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();
                continue;
            }
            return $order;
        }
        return false;
    }

    public function getTaskCount($user)
    {
        $totalCount = $this->getDoctrine()->getRepository('AppBundle:Order')->findAllTaskCount();
        $backCount = $this->getDoctrine()->getRepository('AppBundle:Order')->findAllBackCount($user);
        $todayFinishCount = $this->getDoctrine()->getRepository('AppBundle:Order')->findTodayFinishCount($user);

        $averageTime = $this->getDoctrine()->getRepository('AppBundle:Order')->findTodayAvg($user);
        $averageTime = $averageTime ? $averageTime : 0;

        return ['totalCount' => $totalCount, 'backCount' => $backCount, 'todayFinishCount' => $todayFinishCount, 'averageTime' => $averageTime];
    }

    public function getConfirmCount($user)
    {
        $totalCount = $this->getDoctrine()->getRepository('AppBundle:Order')->findAllConfirmCount();
        $backCount = $this->getDoctrine()->getRepository('AppBundle:Order')->findAllConfirmBackCount($user);
        $todayFinishCount = $this->getDoctrine()->getRepository('AppBundle:Order')->findTodayConfirmCount($user);

        $averageTime = $this->getDoctrine()->getRepository('AppBundle:Order')->findTodayAvg($user);
        $averageTime = $averageTime ? $averageTime : 0;

        return ['totalCount' => $totalCount, 'backCount' => $backCount, 'todayFinishCount' => $todayFinishCount, 'averageTime' => $averageTime];
    }
}
