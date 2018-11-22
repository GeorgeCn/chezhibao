<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Config;
use AppBundle\Entity\Report;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/pingan")
 */
class PinganController extends Controller
{
    /**
     * 根据业务的type id和评估单号来获取数据
     * type为1主要是车的基本信息，为2车价格信息，为3各图片信息
     * 返回结果是json格式
     * @Route("/business/{type}/{orderNo}", name="pingan_business")
     */
    public function businessAction($type, $orderNo)
    {
        try{
            $types = array('1', '2', '3');

            if (!in_array($type, $types)) {
                return new JsonResponse(array('success' => false, 'error_msg' => '错误的type值!'));
            } else {
                $data = $this->get("OrderLogic")->getPinganData($type, $orderNo);

                if ($data) {
                    return new JsonResponse($data);
                } else {
                    return new JsonResponse(array('success' => false, 'error_msg' => '该评估单号查不到结果'));
                }
            }
        }
        catch(Exception $e){
            return new JsonResponse(array('success' => false, 'error_msg' => '错误的type值'));
        }
    }

    /**
     * 异步处理平安最终审核的结果
     * @Route("/handle", name="pingan_handle")
     * @Method({"GET"})
     */
    public function handleAction(Request $request)
    {
        // 获取平安以get请求推送给我们的内容
        $get = $request->query->all();

        $orderNo = $get['orderNo'];
        $status = $get['status'];
        $msg = isset($get['msg']) ? $get['msg']: '';
        $price = $request->get("price", 0);

        if ($price == 0 and $status == 'true') {
            return new JsonResponse(array('success' => false, 'error_msg' => '价格值必传'));
        }

        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->findCompanyOrder(Config::COMPANY_PINGAN, $orderNo);

        if ($order) {
            $report = $order->getReport();
            $reportStatus = $report->getStatus();
            if (0 !== $reportStatus) {
                return new JsonResponse(array('success' => false, 'error_msg' => '该订单已通知过'));
            }

            $originalReport = $report->getReport();

            if ('true' == $status) {
                $backReason = "";
                // 最新价格
                $data['field_4012'] = $price;
            } elseif ('false' == $status && $msg) {
                $backReason = $msg;
                //如果平安最终审核结果为拒绝，需要将拒绝原因插入拒绝原因meta 
                $data['field_result']['value'] = '拒绝放贷';
                $data['field_result']['append']['textarea'] = $msg;
            } else {
                return new JsonResponse(array('success' => false, 'error_msg' => '传的值异常'));
            }

            $bf = $this->get('app.business_factory');
            $mm = $bf->getMetadataManager();
            $oldReport = $report->getPrimaryReport();
            $secReport = $report->getSecReport();
            $reportData = array();
            if (!empty($secReport)) {
                foreach ($mm->getMetadata4Pingan() as $metadata) {
                    if (isset($data[$metadata->key])) {
                        $reportData[$metadata->key] = $metadata->diffValue(@$oldReport[$metadata->key], @$data[$metadata->key]);
                    }
                }
                $report->setSecReport(array_merge($secReport, $reportData));
            } else {
                foreach ($mm->getMetadata4Pingan() as $metadata) {
                    if (isset($data[$metadata->key])) {
                        $reportData[$metadata->key] = $metadata->makeValue($data[$metadata->key]);
                    }
                }
                $report->setReport(array_merge($oldReport, $reportData));
            }

            if (!$reportData) {
                return new JsonResponse(array('success' => false, 'error_msg' => '传的值异常！' ));
            }

            $this->get('ReportLogic')->updateRecheck($report, $backReason);

            return new JsonResponse(array('success' => true));
        } else {
            return new JsonResponse(array('success' => false, 'error_msg' => '该评估单号不存在或状态异常'));
        }
    }
}
