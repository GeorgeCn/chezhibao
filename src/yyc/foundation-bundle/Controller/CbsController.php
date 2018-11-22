<?php
/**
 * 查博士 维保 接口
 *
 * Created by PhpStorm.
 * User: abner
 * Date: 16/11/14
 * Time: 上午11:14
 */
namespace YYC\FoundationBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use YYC\FoundationBundle\Controller\Base\AbstractController;
use YYC\FoundationBundle\Entity\Maintain;


class CbsController extends AbstractController
{
    /*
     * 查询查博士最新数据
     */
    public function newestAction(Request $request)
    {
        //检测是否登录
        $user = $this->isLogin();
        $vin = strtoupper($request->request->get('vin'));
        $origins = $request->request->get('origins');
        //验证重复查询
        $today = $this->getDoctrine()->getManager()->getRepository('YYCFoundationBundle:Maintain')->findTodayByVin($vin);
        if ($today) {
            return new JsonResponse(array('success' => false, 'msg' => '有同事今天已查询过该vin码记录，当天不能重复查询！'));
        }
        //插入一条新的维修记录到数据库中,默认是WAIT状态
        $maintain = new Maintain();
        $maintain->setVin($vin);
        $maintain->setBrandName("");
        $maintain->setOrigins($origins);
        $maintain->setSupplierType(Maintain::TYPE_CBS);
        $maintain->setOperator($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($maintain);
        $em->flush();
        //查询查博士
        $callback = $this->generateUrl('yyc_cbs_notify', array(), UrlGeneratorInterface::ABSOLUTE_URL);
        $Result = $this->get('yyc_foundation.third.cbs')->getBuyReport($vin, $callback);
        $data = json_decode($Result);
        if (0 == $data->Code) {
            //执行查询详情数据
            $this->reportAction($data->orderId, $maintain->getId());
            return new JsonResponse(array('success' => true, 'msg' => "查询" . $data->Message));
        } else {
            /**
             * 各种报错
             *
             * 不存错误信息 - 展示到twig
             */
            $maintain->setStatus(Maintain::STATUS_FAIL);
            $maintain->setRemark($data->Message);
            $em->persist($maintain);
            $em->flush();
            return new JsonResponse(array('success' => false, 'msg' => $data->Message));
        }
    }

    /*
     * 提供到查博士回调的地址
     */
    public function notifyAction(Request $request)
    {
        //获取全部数据
        $data['result'] = $request->get('result');
        $data['orderid'] = $request->get('orderid');
        $data['message'] = $request->get('message');

        $maintain = $this->getDoctrine()->getManager()->getRepository('YYCFoundationBundle:Maintain')->findOneBy(['orderId' => $data['orderid'], 'supplierType'=>Maintain::TYPE_CBS]);

        if(!isset($maintain)){
            return new Response('false');//查无此数据，胡闹!
        }

        if ($maintain->getStatus() > 0) {
            return new Response('success');//如果数据已经处理，将被终止下续操作!
        }

        if ($data['result'] == '1') { //已出报告
            $this->reportAction($data['orderid'], $maintain->getId());
        } else {
            /**
             * 各种报错
             *
             * Code : 2                         //生成报告失败
             * Code : 11011 11012 11013         //数据维护中
             * Code : 12011                     //Vin 码错误/为空
             * Code : 12021                     //车牌号格式不正确/车牌 号为空
             * Code : 12021                     //发动机错误/为空
             * Code : 99999 ......              //未知错误
             */
            $maintain->setStatus(Maintain::STATUS_FAIL);
            $maintain->setRemark($data['message']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($maintain);
            $em->flush();
        }
        return new Response('success');
    }

    /**
     * 查询维修记录
     * @param $orderId
     * @param $maintainId
     *
     * @return JsonResponse
     */
    public function reportAction($orderId, $maintainId)
    {
        //查询新版的维保记录
        $result = $this->get('yyc_foundation.third.cbs')->getNewReportJson($orderId);
        $res_de = json_decode($result);
        $maintain = $this->getDoctrine()->getManager()->getRepository('YYCFoundationBundle:Maintain')->find($maintainId);
        $maintain->setOrderId($orderId);
        if ($res_de->Code == '1104') {// 已出报告
            $maintain->setReturnAt(new \DateTime());
            $maintain->setResults($res_de);
            $maintain->setRemark($res_de->Message);
            $maintain->setStatus(Maintain::STATUS_SUCCESS);
        } elseif ($res_de->Code == '1102') {// 查询中
            $maintain->setRemark($res_de->Message);
            $maintain->setStatus(Maintain::STATUS_WAIT);
        } else {
            /**
             * 各种报错
             *
             * Code : 1000 10001                //用户未授权
             * Code : 1001                      //签名错误
             * Code : 1001 11011 11012 11013    //数据维护中
             * Code : 1105 11051 11052 11053    //报告生成失败
             * Code : 1107 11071                //服务异常
             * Code : 1200                      //无效订单号
             * Code : 1204                      //用户id无效
             * Code : 99999 ......              //未知错误
             */
            $maintain->setRemark($res_de->Message);
            $maintain->setStatus(Maintain::STATUS_FAIL);
        }
        $em = $this->getDoctrine()->getManager();
        $em->persist($maintain);
        $em->flush();
    }

    /**
     * erp 跳转查博士详情页(新版)
     * @param $request
     */
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