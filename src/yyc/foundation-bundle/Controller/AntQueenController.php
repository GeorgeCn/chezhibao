<?php

namespace YYC\FoundationBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use YYC\FoundationBundle\Controller\Base\AbstractController;
use YYC\FoundationBundle\Entity\Maintain;
use AppBundle\Traits\DoctrineAwareTrait;

class AntQueenController extends AbstractController
{
    use DoctrineAwareTrait;

    private $type = Maintain::TYPE_ANTQUEEN;

    public function newestAction(Request $request)
    {
        $vin = strtoupper($request->request->get('vin'));
        $engin = strtoupper($request->request->get('engine'));
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
        $maintain->setSupplierType($this->type);
        $maintain->setOperator($this->getUser());
        $this->persistAndFlushDoctrineManager($maintain);

        $ret = $this->get('yyc_foundation.third.antqueen')->queryByVin($vin, $engin, $maintain->getId());
        if ($ret === false) {
            $lastError = $this->get('yyc_foundation.third.antqueen')->getLastError();
            $maintain->setStatus(Maintain::STATUS_FAIL);
            $maintain->setRemark($lastError);
            $this->flushDoctrineManager();
            return new JsonResponse(['success' => false, 'msg' => $lastError]);
        }
        $maintain->setOrderId($ret);
        $this->flushDoctrineManager();

        return new JsonResponse(['success' => true, 'msg' => "查询成功！"]);
    }

    /*
     * 回调的地址，需要提前注册
     */
    public function notifyAction(Request $request)
    {
        $content = $request->request->all();
        if (empty($content)) {
            return JsonResponse::create("content is empty.");
        }
        $maintain = $this->getRepo('YYCFoundationBundle:Maintain')->findOneBy(['orderId' => $content['query_id'], 'supplierType' => $this->type, 'status' => Maintain::STATUS_WAIT]);
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
        return Response::create("success");
    }
}