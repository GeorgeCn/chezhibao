<?php

namespace AppBundle\Controller\Openapi;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Order;
use AppBundle\Entity\Report;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Model\MetadataManager;
use AppBundle\Traits\DoctrineAwareTrait;
use AppBundle\Entity\User;
use AppBundle\Entity\Config;

/**
 * 
 * @Route("/openapi/v1")
 */

class OrderController extends Controller
{
    use DoctrineAwareTrait;

    /**
     * @Route("/order",name="openapi_get_order")
     * @Method("get")
     */
    public function getOrderAction(Request $request)
    {
        $type = $request->query->get('type', null);
        $start = $request->query->get('start', null);
        $mixed = $request->query->get('mixed', "");
        $limit = $request->query->get('limit', 10);

        if ($type === null || $start === null) {
            return JsonResponse::create([
                    'code' => 2,
                    'msg' => "无效参数",
                ]);
        }

        if($type == 1){
            $ret['data'] = $this->submittedOrders($mixed, $start, $limit);
        }else if($type == 2){
            $ret['data'] = $this->backOrders($start, $limit);
        }
        $ret['code'] = 0;
        $ret['msg'] = 'success';
        return JsonResponse::create($ret);
    }

    private function backOrders($start, $limit)
    {
        $limit = 500;
        $query = $this->getRepo('AppBundle:Order')
            ->findOrderBack($this->getUser()->getId(), true);

        $paginator  = $this->get('knp_paginator');
        $paginations = $paginator->paginate(
            $query, /* query NOT result */
            $start,/*page number*/
            $limit/*limit per page*/
        );

        $ret = [];
        foreach($paginations as $pagination){
            $tmp['id'] = $pagination->getId();
            $tmp['order_no'] = $pagination->getOrderNo();
            $tmp['main_reason'] = $pagination->getLastBack()->getMainReason();
            $tmp['picture'] = $this->getMainPicture($pagination->getPictures());
            $tmp['back_time'] = $pagination->getLastBack()->getCreatedAt()->format("Y-m-d H:i");
            $tmp['companyId'] = $pagination->getCompany() ? $pagination->getCompany()->getId() : 0;
            $ret[] = $tmp;
        }
        return $ret;
    }

    private function submittedOrders($mixed, $start, $limit)
    {
        $query = $this->getRepo('AppBundle:Order')
            ->findOrderSubmitted($this->getUser()->getId(), $mixed, null, null, null, true);

        $paginator  = $this->get('knp_paginator');
        $paginations = $paginator->paginate(
            $query, /* query NOT result */
            $start/*page number*/,
            $limit/*limit per page*/
        );

        $ret = [];
        foreach($paginations as $pagination){
            $tmp['id'] = $pagination->getId();
            $tmp['order_no'] = $pagination->getOrderNo();
            $tmp['valuation'] = $pagination->getValuation();
            $tmp['picture'] = $this->getMainPicture($pagination->getPictures());
            $tmp['submited_at'] = $pagination->getSubmitedAt()->format("Y/m/d H:i");
            $reportStatus = $pagination->getReport() ? $pagination->getReport()->getStatus() : 0;
            $tmp['status'] = $reportStatus;
            $tmp['status_msg'] = $this->getOrderStatus($reportStatus);
            //针对临时用户 status status_msg 设置
            $companyName = $pagination->getLoadOfficer()->getAgencyRels()[0]->getCompany()->getCompany();
            $company = $pagination->getCompany();
            if ($company) {
                $companyName = $company->getCompany();
            }
            $bf = $this->get('app.business_factory');
            $fields = $bf->getFieldPolicy($companyName);
            $tmp['valuation_msg'] = '';
            $tmp['valuation'] = $fields['valuation'] == false ? -1 :$tmp['valuation'];
            $tmp['valuation_check'] = $fields['valuation'] == false ? false :true;
            $tmp['valuation_msg'] = $tmp['valuation'] > 0 ? sprintf("%.3f",($tmp['valuation']/10000))."万" : '';

            if($pagination->getLoadOfficer()->getType() == User::TYPE_TEMP){
                $tmp['status'] = 3;
                $tmp['status_msg'] = "已提交";
            }

            if($tmp['status'] == Report::STATUS_PASS){
                $tmpReport = $pagination->getReport()->getReport();
                //将废弃字段
                $tmp['valuation'] = 0;
                //将废弃字段
                $tmp['valuation_check'] = true;

                $tmp['valuation_msg'] = $this->getPurchaseSellPriceMsg($pagination, $fields);
                if (empty($tmp['valuation_msg'])) {
                    $tmp['valuation_check'] = false;
                }
            }
            if($tmp['status'] == Report::STATUS_REFUSE){
                $orderReport = $pagination->getReport()->getReport();
                $tmp['main_reason'] = isset($orderReport['field_result']['options']['textarea']) ? $orderReport['field_result']['options']['textarea'] : '';
                $hpl_reason = $pagination->getReport()->getHplReason();
                $tmp['main_reason'] = $hpl_reason? : $tmp['main_reason'];
            }
            //最后判断$tmp['valuation_check']
            $tmp['valuation_check'] = ($tmp['valuation_msg'] == '') ? false : $tmp['valuation_check'];
            $tmp['companyId'] = $pagination->getCompany() ? $pagination->getCompany()->getId() : 0;
            $ret[] = $tmp;
        }
        return $ret;
    }

    private function getMainPicture($pictures)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $k = $mm->getMainPictureKey();
        return isset($pictures[$k]) && count($pictures[$k]) !=0 ? $pictures[$k][0] : '';
    }

    /**
     * @Route("/order",name="openapi_order_postorder")
     * @Method("post")
    */
    public function postOrderAction(Request $request)
    {
        $user = $this->getUser();
        $valuation = $request->request->get('valuation', 0);
        $pictures = $request->request->get('pictures', null);
        $extras = $request->request->get('extras', '');
        $remark = $request->request->get('remark', null);
        $videos = $request->request->get('videos', []);
        $longitude = $request->request->get('longitude', null);
        $latitude = $request->request->get('latitude', null);
        $loan_number = trim($request->request->get('loan_number', ""));
        $photorecord = $request->request->get('photorecord', "");
        $parentId = $request->request->get('parentId', null);
        $companyId = $request->request->get('companyId', null);

        $companyName = $user->getAgencyRels()[0]->getCompany()->getCompany();
        if ($companyId) {
            $company = $this->getRepo('AppBundle:Config')->find($companyId);
            if ($company) {
                // 覆盖取之前信贷员的公司逻辑
                $companyName = $company->getCompany();
            }
        }
        
        $orderLogic = $this->get("OrderLogic");

        if ($extras) {
            $extras = json_decode($extras, true);
            if (isset($extras['businessNumber']) && $extras['businessNumber']) {
                $loan_number = $extras['businessNumber'];
            }
        }

        if($loan_number && $orderLogic->findOrderByCompanyNumber($loan_number, $companyName)) {
            return JsonResponse::create(['code' => 7, 'msg' => '业务流水号已使用']);
        }
        $syncObject = $this->get('app.business_factory')->getSystemSyncObject($companyName);
        $ret = $syncObject->validateNumber($loan_number, $this->container);
        if(!$ret) {
            return JsonResponse::create(['code' => 6, 'msg' => '业务流水号错误']);
        }

        if($pictures === null){
            return JsonResponse::create(['code' => 2, 'msg' => '无效参数']);
        }

        $pictures = json_decode($pictures, true);
        if(!is_array($pictures)){
            return JsonResponse::create(['code' => 3, 'msg' => '图片信息不正确']);
        }

        if(!empty($videos)) {
            $videos = json_decode($videos, true);
        }

        $order = $orderLogic->createOrder($user);
        $newremark = ['remark'=>$remark];
        $newvaluation = ['valuation'=>$valuation];
        $newlongitude = ['longitude'=>$longitude];
        $newlatitude = ['latitude'=>$latitude];
        $loan_number = ['businessNumber'=>$loan_number];
        $parentId = ['parentId' => $parentId];
        $companyId = ['companyId' => $companyId];
        $extras = ['extras'=>$extras];
        $newposts = array_merge($pictures, $videos, $newvaluation, $newremark, $newlongitude, $newlatitude, $loan_number, $extras, $parentId, $companyId);

        //经纬度转换地址字段处理 判断 true 表示 新提交的订单
        $order->add_order_address = true;
        //图片上传操作记录
        $order->photorecord = $photorecord;
        $orderLogic->updateOrder($order, $newposts, true, true);

        return JsonResponse::create(['code' => 0, 'msg' => 'success', 'data' =>$order->getId()]);
    }

    /**
     * 已退回再提交的order 
     * @Route("/order/{id}",name="openapi_order_putorder")
     * @Method("put")
    */
    public function putOrderAction($id, Request $request)
    {
        if($id === null){
            return JsonResponse::create(['code' => 4, 'msg' => '无效参数']);
        }

        $append = $request->request->get('append', null);
        $appendVideo = $request->request->get('append_video', null);
        $longitude = $request->request->get('longitude', null);
        $latitude = $request->request->get('latitude', null);
        $append = json_decode($append, true);
        $appendVideo = json_decode($appendVideo, true);
        if(empty($append) && empty($appendVideo)) {          
            return JsonResponse::create(['code' => 4, 'msg' => '无效参数']);
        } 
        
        $order = $this->getRepo('AppBundle:Order')->find($id);
        if(!$order){
            return JsonResponse::create(['code' => 5, 'msg' => '无效订单']);
        }

        $user = $this->getUser();
        $check = $this->checkOrderOwner($order, Order::STATUS_EDIT, $user);
        if($check){
            return JsonResponse::create($check);
        }
        $posts = [
            'append'=>$append,
            'append_video'=>$appendVideo
            ];

        $orderLogic = $this->get("OrderLogic");

        $orderLogic->updateOrder($order, $posts, true);
        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' =>$order->getId()]);
    }

    /**
     * @Route("/order/{id}",name="openapi_order_getorderdetail")
     * @Method("get")
    */
    public function getOrderDetailAction($id, Request $request)
    {
        $order = $this->getRepo('AppBundle:Order')->find($id);
        if(!$order){
            return JsonResponse::create(['code' => 4, 'msg' => '订单不存在']);
        }
        $user = $this->getUser();
        $orderLogic = $this->get("OrderLogic");

        $vars = $this->getArrShortOrder($order);
        $back = $order->getLastBack();
        $check = $this->checkOrderOwner($order, Order::STATUS_EDIT, $user);

        if($check){
            if($check['code'] == 2){
                return JsonResponse::create(['code' => 0, 'msg' => 'success', 'data' =>$vars]);
            }
            return JsonResponse::create($check);
        }

        if (empty($back)) {
            // 过滤掉append的metadata，因为append只有在退回编辑的时候用到。
            return JsonResponse::create(['code' => 0, 'msg' => 'success', 'data' =>$vars]);
        }
        $backReasonKeys = $orderLogic->backReasonKeyMetadata();
        $companyName = $order->getCompany()->getCompany();
        $backReasonVideoKeys = $orderLogic->backReasonVideoKeyMetadata($companyName);
        $reason_metadatas = $orderLogic->matchBackReasonMetas($back->getReason());
        $reason_metadatas_video = $orderLogic->matchBackReasonVideoMetas($back->getReason());

        if(empty($reason_metadatas)) {
            $vars['backreason'] = [];
        } else {
            foreach($reason_metadatas as $k=>$v){
                $vars['backreason'][$k]['key'] = $v->key;
                $vars['backreason'][$k]['reason'] = $v->value['value'];
                $vars['backreason'][$k]['sample'] = $backReasonKeys[$v->key]['sample'];
                $vars['backreason'][$k]['sample_full'] = $this->getParameter('qiniu_domain').'/'.$backReasonKeys[$v->key]['sample'];
                $vars['backreason'][$k]['display'] = $backReasonKeys[$v->key]['display'];
            }
        }
        if(empty($reason_metadatas_video)) {
            $vars['backreason2'] = [];
        } else {
            foreach($reason_metadatas_video as $k=>$v){
                $vars['backreason2'][$k]['key'] = $v->key;
                $vars['backreason2'][$k]['reason'] = $v->value['value'];
                $vars['backreason2'][$k]['sample'] = $backReasonVideoKeys[$v->key]['sample'];
                $vars['backreason2'][$k]['sample_full'] = $this->getParameter('qiniu_domain').'/'.$backReasonVideoKeys[$v->key]['sample'];
                $vars['backreason2'][$k]['display'] = $backReasonVideoKeys[$v->key]['display'];
            }
        }
        
        $vars['main_reason'] = $back->getMainReason();

        return JsonResponse::create(['code' => 0, 'msg' => 'success', 'data' =>$vars]);
    }

    /**
     * 撤单，已退回逻辑删除(disable为1)
     * @Route("/order/{id}",name="openapi_order_deleteorder")
     * @Method("delete")
    */
    public function deleteOrderAction($id)
    {
        $result = [];
        $order = $this->getRepo('AppBundle:Order')->find($id);
        if(!$order){
            $result['code'] = 4;
            $result['msg'] = '无效订单！';
            return JsonResponse::create($result);
        }
        $user = $this->getUser();
        $check = $this->checkOrderOwner($order, Order::STATUS_EDIT, $user);
        if($check){
            return JsonResponse::create($check);
        }
        $order->setDisable(1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $result['code'] = 0;
        $result['msg'] = '撤单成功';
        $result['data'] = $id;

        return JsonResponse::create($result);
    }

    private function getArrShortOrder($order)
    {
        if(!$order){
            return [];
        }
        $orderarr = [];
        $orderarr['id'] = $order->getId();
        $orderarr['order_no'] = $order->getOrderNo();
        $orderarr['valuation'] = $order->getValuation();
        $orderarr['remark'] = $order->getRemark();

        $orderLogic = $this->get('OrderLogic');
        $orderarr['loan_number'] = $order->getBusinessNumber()? : "";
        $orderarr['extras'] = $orderLogic->getExtraFieldsValue($order);

        //根据Metadata 把 display group 放进对应的图片上
        //结合临时用户修改 $orderLogic 
        $companyName = $order->getLoadOfficer()->getAgencyRels()[0]->getCompany()->getCompany();

        $company = $order->getCompany();
        if ($company) {
            // 覆盖取之前信贷员的公司逻辑
            $companyName = $company->getCompany();
        }

        $picturesKeyMetadata = $orderLogic->getPicturesKeyMetaDatas($companyName);
        $pictures = $order->getPictures();
        $newPictures = [];
        foreach($pictures as $key=>$value){
            $checkPicture = [];
            if(strpos($key, 'append') === false){
                $checkPicture['key'] = $key;
                $checkPicture['display'] = isset($picturesKeyMetadata[$key]['display']) ? $picturesKeyMetadata[$key]['display'] : "";
                $checkPicture['group'] = isset($picturesKeyMetadata[$key]['group']) ? $picturesKeyMetadata[$key]['group'] : "";
                $checkPicture['value'] = $value;
            }else{
                $checkPicture['key'] = $key;
                $checkPicture['display'] = '补充';
                $checkPicture['group'] = '补充';
                $checkPicture['value'] = $value;
            }
            $newPictures[] = $checkPicture;
        }
        $orderarr['pictures'] = $newPictures;

        $reportStatus = $order->getReport() ? $order->getReport()->getStatus() : 0;
        $orderarr['status'] = $reportStatus;
        $orderarr['status_msg'] = $this->getOrderStatus($reportStatus);

        //显示判断
        $bf = $this->get('app.business_factory');
        $fields = $bf->getFieldPolicy($companyName);

        if($orderarr['status'] == Report::STATUS_PASS){
                $tmpReport = $order->getReport()->getReport();
                //将废弃字段
                $orderarr['report_valuation'] = 0;

                //收购价 销售价
                $orderarr['report_purchase_sell'] = $this->getPurchaseSellPriceNum($order, $fields);
                //将废弃字段
                $orderarr['report_valuation_check'] = true;
                //如果没有数据 设置app 不显示策略
                if(empty($orderarr['report_purchase_sell'])){
                    $orderarr['report_valuation_check'] = false;
                    $orderarr['report_valuation_check'] = false;
                } 
            }
        if($orderarr['status'] == Report::STATUS_REFUSE){
                $orderReport = $order->getReport()->getReport();
                $orderarr['main_reason'] = isset($orderReport['field_result']['options']['textarea']) ? $orderReport['field_result']['options']['textarea'] : '';
                $hpl_reason = $order->getReport()->getHplReason();
                $orderarr['main_reason'] = $hpl_reason? : $orderarr['main_reason'];
            }

        $orderarr['valuation'] = $fields['valuation'] == false ? -1 :$orderarr['valuation'];
        $orderarr['valuation_check'] = $fields['valuation'] == false ? false :true;

        if ($reportStatus != Report::STATUS_WAIT && $order->getStatus() != Order::STATUS_EDIT && $fields['report']) {
            $orderarr['report_url'] = $this->generateUrl("app_report", ['orderid' => $order->getId()]);
        }

        $orderarr['isCloneable'] = $orderLogic->isCloneable($order);

        //业务调整新增video的meta
        if($fields['video']) {
            $videosKeyMetadata = $orderLogic->getVideosKeyMetaDatas($companyName);
            $videos = $order->getVideos();
            $newVideos = $tmpVideo = []; 
            if(!empty($videos)) {
                foreach($videos as $key=>$value){
                    $tmpVideo['key'] = $key;
                    $tmpVideo['display'] = isset($videosKeyMetadata[$key]['display']) ? $videosKeyMetadata[$key]['display'] : "";
                    $tmpVideo['group'] = isset($videosKeyMetadata[$key]['group']) ? $videosKeyMetadata[$key]['group'] : "";
                    $tmpVideo['value'] = $value;
                    $newVideos[] = $tmpVideo;
                }
            }
            $orderarr['videos'] = $newVideos;
            return $orderarr;
        } else {
            return $orderarr;
        }
    }

    //根据order status 数值判断order 状态
    private function getOrderStatus($reportStatus)
    {
        $statusmsg = '';
        switch ($reportStatus) {
            case 0:
                $statusmsg = '评估中';
                break;
            case 1:
                $statusmsg = '审核完毕';
                break;
            case 2:
                $statusmsg = '审核失败';
                break;
            default:
                break;
        }
        return $statusmsg;

    }

    /***********private 公用函数*****************/
    private function checkOrderOwner(Order $order, $status, $user)
    {
        if ($order->getStatus() !== $status) {
            $data = [
                'code' => 2,
                'msg' => '检测订单状态不对！',
                ];
            return $data;
        }

        if ($status === Order::STATUS_EDIT && $order->getLoadOfficer() !== $user) {
            $data = [
                'code' => 3,
                'msg' => '这不是你的检测订单！',
                ];
            return $data;
        }
        return false;
    }

    public function getPurchaseSellPriceMsg($order, $fields = [])
    {
        $tmpReport = $order->getReport()->getReport();
        //收购价
        $remsg = '';
        $tmp['purchasePrice_msg'] = '';
        $tmp['purchasePrice'] = isset($tmpReport['field_4010']['value']) ? (int)$tmpReport['field_4010']['value'] : 0;
        $tmp['purchasePrice_num'] = ($tmp['purchasePrice'] > 0) ? floatval($tmp['purchasePrice']/10000) : 0;
        //销售价
        $tmp['sellPrice_msg'] = '';
        $tmp['sellPrice'] = isset($tmpReport['field_4012']['value']) ? (int)$tmpReport['field_4012']['value'] : 0;
        $tmp['sellPrice_num'] = ($tmp['sellPrice'] > 0) ? floatval($tmp['sellPrice']/10000) : 0;


        //getFieldPolicy 判断
        $tmp['purchasePrice_num'] = $fields['purchasePriceApp'] == false ? 0 : $tmp['purchasePrice_num'];
        $tmp['sellPrice_num'] = $fields['sellPriceApp'] == false ? 0 : $tmp['sellPrice_num'];

        if($tmp['purchasePrice_num']>0 && $tmp['sellPrice_num'] >0){
            $remsg = $tmp['purchasePrice_num']."万"."(收购价)  ".$tmp['sellPrice_num']."万"."(销售价)";
        }else if ($tmp['purchasePrice_num']>0 && $tmp['sellPrice_num'] <=0){
            $remsg = $tmp['purchasePrice_num']."万";
        }else if ($tmp['purchasePrice_num'] <=0 && $tmp['sellPrice_num'] >0){
            $remsg = $tmp['sellPrice_num']."万";
        }else{
            $remsg = '';
        }
        return $remsg;
    }

    public function getPurchaseSellPriceNum($order, $fields = [])
    {
        $re = [];
        $rearr = [];
        $tmpReport = $order->getReport()->getReport();
        //收购价 销售价
        $rearr[0]['price'] = isset($tmpReport['field_4010']['value']) ? (int)$tmpReport['field_4010']['value'] : 0;
        $rearr[0]['price_msg'] = "收购价格(元)";
        $rearr[1]['price'] = isset($tmpReport['field_4012']['value']) ? (int)$tmpReport['field_4012']['value'] : 0;
        $rearr[1]['price_msg'] = "销售价格(元)";

        //getFieldPolicy 判断
        $rearr[0]['price'] = $fields['purchasePriceApp'] == false ? 0 : $rearr[0]['price'];
        $rearr[1]['price'] = $fields['sellPriceApp'] == false ? 0 : $rearr[1]['price'];

        foreach($rearr as $k=>$v){
            if($v['price']>0){
                $re[] = $v;
            }
        }
        return $re;
    }

    /**
     * @Route("/validateNumber",name="openapi_validate_number")
     * @Method("post")
    */
    public function validateNumberAction(Request $request)
    {
        $companyName = $this->getUser()->getAgencyRels()[0]->getCompany()->getCompany();
        $number = $request->request->get('number', '');
        $companyId = $request->request->get('companyId', '');
        if ($companyId) {
            $company = $this->getRepo('AppBundle:Config')->find($companyId);
            if ($company) {
                $companyName = $company->getCompany();
            }
        }
        
        if($number && $this->get("OrderLogic")->findOrderByCompanyNumber($number, $companyName)) {
            return JsonResponse::create(['code' => 1, 'msg' => '业务流水号已使用']);
        }
        $syncObject = $this->get('app.business_factory')->getSystemSyncObject($companyName);
        $ret = $syncObject->validateNumber($number, $this->container);
        if($ret) {
            return JsonResponse::create(['code' => 0, 'msg' => 'success']);
        }
        return JsonResponse::create(['code' => 2, 'msg' => '业务流水号错误']);
    }
}