<?php

namespace YYC\FoundationBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use YYC\FoundationBundle\Controller\Base\AbstractController;
use YYC\FoundationBundle\Entity\Maintain;
use AppBundle\Traits\DoctrineAwareTrait;

class JuheController extends AbstractController
{
    use DoctrineAwareTrait;

    /*
     * 查询聚合数据
     */
    public function newestAction(Request $request)
    {
        $vin = strtoupper($request->request->get('vin'));
        $origins = $request->request->get('origins');
        //验证重复查询
        $today = $this->getRepo('YYCFoundationBundle:Maintain')->findTodayByVin($vin);
        if ($today) {
            return new JsonResponse(array('success' => false, 'msg' => '有同事今天已查询过该vin码记录，当天不能重复查询！'));
        }

        //插入一条新的维修记录到数据库中,默认是WAIT状态
        $maintain = new Maintain();
        $maintain->setVin($vin);
        $maintain->setBrandName("");
        $maintain->setOrigins($origins);
        $maintain->setSupplierType(Maintain::TYPE_JUHE);
        $maintain->setOperator($this->getUser());
        $this->persistAndFlushDoctrineManager($maintain);

        $ret = $this->get('yyc_foundation.third.Juhe')->submitMaintenanceOrder($vin);
        if ($ret === false) {
            $maintain->setStatus(Maintain::STATUS_FAIL);
            $maintain->setRemark("聚合数据提交订单出错！");
            $this->flushDoctrineManager();
            return new JsonResponse(['success' => false, 'msg' => "聚合数据提交订单出错！"]);
        }
        $maintain->setOrderId($ret["order_id"]);
        $this->flushDoctrineManager();

        return new JsonResponse(['success' => true, 'msg' => "查询成功！"]);
    }

    /*
     * 回调的地址，需要提前注册
     */
    public function notifyAction(Request $request)
    {
        $content = $request->getContent();
        $content = json_decode($content, true);
        if (empty($content)) {
            return JsonResponse::create("content is empty.");
        }
        $maintain = $this->getRepo('YYCFoundationBundle:Maintain')->findOneBy(['orderId' => $content['order_id'], 'supplierType' => Maintain::TYPE_JUHE, 'status' => Maintain::STATUS_WAIT]);
        if (empty($maintain)) {
            return JsonResponse::create("can't find maintain.");
        }

        $maintain->setReturnAt(new \DateTime());
        $maintain->setResults($content);
        $maintain->setStatus(Maintain::STATUS_FAIL);
        if ($content["result_status"] == "QUERY_SUCCESS") {
            $maintain->setStatus(Maintain::STATUS_SUCCESS);
        }
        $this->flushDoctrineManager();
        return JsonResponse::create();
    }

    // ?
    public function goAction(Request $request)
    {
        $id = $request->get('id');
        $maintain = $this->getDoctrine()->getManager()->getRepository('YYCFoundationBundle:Maintain')->find($id);
        $orderId = $maintain->getOrderId();
        $url = $result = $this->get('yyc_foundation.third.cbs')->getNewReportUrl($orderId);
        header("Location: $url[0]");
        exit;
    }

}