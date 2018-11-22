<?php

namespace AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/hpl")
 */
class HplController extends Controller
{
    /**
     * 根据业务的type id和评估单号来获取数据
     * type为1主要是车的基本信息，为2车价格信息，为3各图片信息
     * 返回结果是json格式
     * @Route("/business/{type}/{orderNo}", name="hpl_business")
     */
    public function businessAction($type, $orderNo)
    {
        try{
            $types = array('1', '2', '3');

            if (!in_array($type, $types)) {
                return new JsonResponse(array('success' => false, 'error_msg' => '错误的type值!'));
            } else {
                $data = $this->get("OrderLogic")->getHplData($type, $orderNo);

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
}
