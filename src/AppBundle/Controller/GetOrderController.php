<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\util\SessionApiLimiter;

/**
 *
 * @Route("/order/task")
 */
class GetOrderController extends Controller
{
    /**
     * ajax接单
     * @Security("has_role('ROLE_EXAMER')")
     * @Route("/order", name="get_order")
     */
    public function getOrderAction(Request $request)
    {
        $ret = SessionApiLimiter::check($request->getSession(), "getorder");
        if ($ret === false) {
            return JsonResponse::create(['code' => 3, 'msg' => '请勿频繁刷新系统，谢谢！']);
        }

        $ret = $this->get("GetOrderLogic")->getOrder($this->getUser());
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
        $data['orderNo'] = $order->getOrderNo();
        $data['name'] = $order->getloadOfficer()->getName();
        $data['mobile'] = $order->getloadOfficer()->getMobile();
        $data['company'] = $order->getCompany()->getCompany();
        $data['companyCode'] = $order->getAgencyCode();
        $data['examer'] = $order->getReport() ? $order->getReport()->getExamer()->getName() : '';

        if ($order->getStatus() == 1 && $order->getReport() && $order->getReport()->getHplReason()) {
            $data['status'] = '第三方退回';
        } elseif ($order->getStatus() == 1 && $order->getLastBack()) {
            $data['status'] = '重新提交';
        } else {
            $data['status'] = '初次提交';
        }

        $data['submittedAt'] = $order->getSubmitedAt()->format('Y-m-d H:i:s');
        $data['finishAt'] = $order->getSubmitedAt()->modify('+1 hour')->format('Y-m-d H:i:s');

        return JsonResponse::create(['code' => 0, 'data' => $data]);
    }

    /**
     * 刷新页面接单
     * @Security("has_role('ROLE_EXAMER')")
     * @Route("/", name="order_task")
     */
    public function taskAction(Request $request)
    {
        $title = '接单';
        $order = null;
        $limit = !SessionApiLimiter::check($request->getSession(), "getorder");
        if ($limit === false) {
            $order = $this->get("GetOrderLogic")->getLockedOrder($this->getUser());
        }

        return $this->render('getOrder/task.html.twig', array(
            'title' => $title,
            'order' => $order,
            'limit' => $limit,
            'orderLogic' => $this->get('OrderLogic'),
        ));
    }

    /**
     * ajax统计
     * @Security("has_role('ROLE_EXAMER')")
     * @Route("/taskCount", name="order_task_count")
     * @Cache(maxage="3")
     */
    public function taskCountAction(Request $request)
    {
        $ret = $this->get("GetOrderLogic")->getTaskCount($this->getUser());

        return JsonResponse::create($ret);
    }
}
