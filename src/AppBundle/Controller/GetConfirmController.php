<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
 * @Route("/order/getconfirm")
 */
class GetConfirmController extends Controller
{
    /**
     * ajax接单
     * @Security("has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/order", name="order_getconfirm_order")
     */
    public function getOrderAction(Request $request)
    {
        $ret = $this->get("GetOrderLogic")->getConfirm($this->getUser());
        if ($ret['code'] === 1) {
            return JsonResponse::create(['code' => 1, 'msg' => '没单子了，请休息一下！']);
        }

        if ($ret['code'] === 2) {
            return JsonResponse::create(['code' => 2, 'msg' => '今天辛苦了，请下班回家吧！']);
        }

        if ($ret['order']) {
            $order = $ret['order'];
        }

        $data = [];
        $data['id'] = $order->getId();
        $data['reportId'] = $order->getReport()->getId();
        $data['orderNo'] = $order->getOrderNo();
        $data['name'] = $order->getloadOfficer()->getName();
        $data['mobile'] = $order->getloadOfficer()->getMobile();
        $data['company'] = $order->getCompany()->getCompany();
        $data['companyCode'] = $order->getAgencyCode();
        $data['rechecker'] = $order->getReport()->getRechecker() ? $order->getReport()->getRechecker()->getName() : '';

        if ($order->getStatus() == 1 && $order->getReport() && $order->getReport()->getHplReason()) {
            $data['status'] = '第三方退回';
        } elseif ($order->getStatus() == 1 && $order->getLastBack()) {
            $data['status'] = '重新提交';
        } else {
            $data['status'] = '初次复审';
        }

        $data['submittedAt'] = $order->getSubmitedAt()->format('Y-m-d H:i:s');
        $data['finishAt'] = $order->getSubmitedAt()->modify('+1 hour')->format('Y-m-d H:i:s');

        return JsonResponse::create(['code' => 0, 'data' => $data]);
    }

    /**
     * 刷新页面接单
     * @Security("has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/", name="order_getconfirm")
     */
    public function taskAction(Request $request)
    {
        $title = '复审接单';
        $order =  $this->get("GetOrderLogic")->getConfirmOrder($this->getUser());
        $ret = $this->get("GetOrderLogic")->getConfirmCount($this->getUser());

        return $this->render('getConfirm/confirm.html.twig', array(
            'title' => $title,
            'order' => $order,
            'ret' => $ret,
            'orderLogic' => $this->get('OrderLogic'),
        ));
    }


    /**
     * ajax统计
     * @Security("has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/count", name="order_getconfirm_count")
     */
    public function taskCountAction(Request $request)
    {
        $ret = $this->get("GetOrderLogic")->getConfirmCount($this->getUser());

        return JsonResponse::create($ret);
    }

}
