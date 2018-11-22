<?php

namespace AppBundle\Controller;

use AppBundle\Event\OrderExamEvent;
use AppBundle\Event\OrderRecheckEvent;
use AppBundle\Model\MetadataCustomerManager;
use AppBundle\Model\MetadataManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Order;
use AppBundle\Entity\Report;
use Symfony\Component\EventDispatcher\EventDispatcher;
use AppBundle\EventSubscriber\OrderBackSubscriber;
use AppBundle\EventSubscriber\OrderFinishSubscriber;
use AppBundle\Event\HplEvents;
use AppBundle\Event\OrderBackEvent;
use AppBundle\Event\OrderFinishEvent;
use AppBundle\Entity\User;
use AppBundle\Entity\Config;
use Zend\Serializer\Adapter\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\StageLog;
use AppBundle\Event\OrderPrimaryExamEvent;

/**
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * @Security("has_role('ROLE_EXAMER')")
     * @Route("/check/{id}", name="task_check")
     * @ParamConverter("report", class="AppBundle:Report")
     */
    public function checkAction(Report $report, $id)
    {
        if (!$report->getCreatedAt()) {
            $report->setCreatedAt(new \DateTime());
        }

        $report->setExamer($this->getUser());
        $report->setLocked(true);
        $this->getDoctrine()->getManager()->flush();
        
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        foreach ($mm->getMetadata4CheckArray() as $metadatas) {
            $domsArray[] = $mm->buildDom($metadatas, $report->getReport());
        }
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('AppBundle:Report')->findOrder($id);

        $orderLogic = $this->get("OrderLogic");
        list($metadatas, $append_metadata, $groups) = $orderLogic->getMetadatas(true, $getGroups = true, $order->getCompany()->getCompany());
        // 覆写meta顺序及新增group，特殊写死处理
        $metadatas = $orderLogic->getSortedMetadatas($metadatas);
        $groups = ['证件照','车况', '车型图', '附加'];

        //获取该订单的相关公司配置信息及meta字段是否隐藏信息
        $orderCompanyName = $order->getCompany()->getCompany();
        $companyConfig = $order->getCompany();
        $fieldDisplay = $this->get('app.business_factory')->getFieldPolicy($orderCompanyName);
        $isHpl = $orderCompanyName === Config::COMPANY_HPL || $orderCompanyName === Config::COMPANY_HPL_CBT ? true : false;
        $isMct = $orderCompanyName === Config::COMPANY_MCT ? true : false;
        $isCzb = $order->getAgencyName() === '江苏车置宝信息科技股份有限公司' && $order->getCompany()->getCompany() === Config::COMPANY_HPL ? true : false;

        $examStandard = $companyConfig->getJytj()['k6'];
        $limitMinutes = isset($companyConfig->getJytj()['k7']) ? $companyConfig->getJytj()['k7'] : 0;
        $submittedAt = clone($order->getSubmitedAt());
        $start = $submittedAt->modify("+$limitMinutes minutes");
        $end = new \DateTime();

        //2个时间相差的间隔
        $interval = $start->diff($end);
        $submittedAt2 = clone($order->getSubmitedAt());
        $start2 = $submittedAt2->modify("+30 minutes");
        $interval2 = $start2->diff($end);

        if ($interval2->invert == 1) {
            $days = $interval2->days;
            // 算出相差的秒数
            $remainSeconds = ($days * 24 * 60 + $interval2->h * 60 + $interval2->i) * 60 + $interval2->s;
        } else {
            $remainSeconds = 0;
        }

        $vars = [
            'id' => $id,
            'order' => $order,
            'metadatas' => $metadatas,
            'domsArray' => $domsArray,
            'reportLogic' => $this->get('ReportLogic'),
            'append_metadata' => $append_metadata,
            'report' => $report,
            'groups' => $groups,
            'businessFactory' => $bf,
            'examStandard' => $examStandard,
            'limitMinutes' => $limitMinutes,
            'interval' => $interval,
            'fieldDisplay' => $fieldDisplay,
            'isHpl' => $isHpl,
            'isMct' => $isMct,
            'isCzb' => $isCzb,
            'remainSeconds' => $remainSeconds,
            'hasData' => $report->getReport() ? true :false,
        ];

        $vars['fieldDisplay'] = $fieldDisplay;

        if (!empty($order->getLastBack())) {
            $vars["main_reason"] = $order->getLastBack()->getMainReason();
            $vars["reason_metadatas"] = $orderLogic->matchBackReasonMetas($order->getLastBack()->getReason());
        }
        $userType = $order->getLoadOfficer()->getType();
        $remark = $order->getRemark();
        $videos = $order->getVideos(); 
        $orderLogic = $this->get('OrderLogic');
        //经纬度转地址查看
        $vars['order_address'] = $order->getOrderAddress();
        if(!$vars['order_address']){
            $vars['order_address'] = $orderLogic->updateOrderAddress($order);
        }
        if(empty($videos)) {
            $vars['videoSrc'] = '';
        } else {
            if(!empty($videos['append_video'])) {
                $vars['videoSrc'] = $this->getParameter('qiniu_domain').'/'.array_pop($videos['append_video']);
            } else {
                $vars['videoSrc'] = $this->getParameter('qiniu_domain').'/'.$videos['v1'][0];
            }
        }
        if ($userType == User::TYPE_TEMP) {
            $vars['remark'] = json_decode($remark);
            $vars['questions'] = $orderLogic->getAskQuertionMetadata();
            return $this->render('check/checkCustomer.html.twig', $vars);
        }
        if ($orderCompanyName == Config::COMPANY_JGQC) {
            $vars['remark'] = json_decode($remark);
            $vars['questions'] = $orderLogic->getAskQuertionMetadata();
        }
        
        return $this->render('check/check.html.twig', $vars);
    }

    /**
     * @Security("has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/confirm/{id}", name="task_confirm")
     */
    public function confirmAction(Report $report, $id)
    {
        if (!$report->getCreatedAt()) {
            $report->setCreatedAt(new \DateTime());
        }

        $report->setRechecker($this->getUser());
        $report->setLocked(true);
        //设置复审开始时间
        $report->setStartAt(new \DateTime());
        $this->getDoctrine()->getManager()->flush();
        
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        foreach ($mm->getMetadata4CheckArray() as $metadatas) {
            $domsArray[] = $mm->buildDom($metadatas, $report->getReport());
        }
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('AppBundle:Report')->findOrder($id);


        $orderLogic = $this->get("OrderLogic");
        list($metadatas, $append_metadata, $groups) = $orderLogic->getMetadatas(true, $getGroups = true, $order->getCompany()->getCompany());
        // 覆写meta顺序及新增group，特殊写死处理
        $metadatas = $orderLogic->getSortedMetadatas($metadatas);
        $groups = ['证件照','车况', '车型图', '附加'];

        //获取该订单的相关公司配置信息及meta字段是否隐藏信息
        $orderCompanyName = $order->getCompany()->getCompany();
        $companyConfig = $order->getCompany();
        $fieldDisplay = $this->get('app.business_factory')->getFieldPolicy($orderCompanyName);
        $isHpl = $orderCompanyName === Config::COMPANY_HPL || $orderCompanyName === Config::COMPANY_HPL_CBT ? true : false;
        $isMct = $orderCompanyName === Config::COMPANY_MCT ? true : false;
        $isCzb = $order->getAgencyName() === '江苏车置宝信息科技股份有限公司' && $order->getCompany()->getCompany() === Config::COMPANY_HPL ? true : false;

        $examStandard = $companyConfig->getJytj()['k6'];
        $limitMinutes = isset($companyConfig->getJytj()['k7']) ? $companyConfig->getJytj()['k7'] : 0;//审核时限
        $submittedAt = clone($order->getSubmitedAt());
        $start = $submittedAt->modify("+$limitMinutes minutes");
        $end = new \DateTime();

        //2个时间相差的间隔
        $interval = $start->diff($end);
        $submittedAt2 = clone($order->getSubmitedAt());
        $start2 = $submittedAt2->modify("+30 minutes");
        $interval2 = $start2->diff($end);

        if ($interval2->invert == 1) {
            $days = $interval2->days;
            // 算出相差的秒数
            $remainSeconds = ($days * 24 * 60 + $interval2->h * 60 + $interval2->i) * 60 + $interval2->s;
        } else {
            $remainSeconds = 0;
        }

        $vars = [
            'id' => $id,
            'order' => $order,
            'metadatas' => $metadatas,
            'domsArray' => $domsArray,
            'reportLogic' => $this->get('ReportLogic'),
            'append_metadata' => $append_metadata,
            'report' => $report,
            'groups' => $groups,
            'businessFactory' => $bf,
            'examStandard' => $examStandard,
            'limitMinutes' => $limitMinutes,
            'interval' => $interval,
            'fieldDisplay' => $fieldDisplay,
            'isHpl' => $isHpl,
            'isMct' => $isMct,
            'isCzb' => $isCzb,
            'remainSeconds' => $remainSeconds,
            'hasData' => $report->getReport() ? true :false,
        ];

        $vars['fieldDisplay'] = $fieldDisplay;

        if (!empty($order->getLastBack())) {
            $vars["main_reason"] = $order->getLastBack()->getMainReason();
            $vars["reason_metadatas"] = $orderLogic->matchBackReasonMetas($order->getLastBack()->getReason());
        }
        $userType = $order->getLoadOfficer()->getType();
        $remark = $order->getRemark();
        $videos = $order->getVideos(); 
        $orderLogic = $this->get('OrderLogic');
        //经纬度转地址查看
        $vars['order_address'] = $order->getOrderAddress();
        if(!$vars['order_address']){
            $vars['order_address'] = $orderLogic->updateOrderAddress($order);
        }
        if(empty($videos)) {
            $vars['videoSrc'] = '';
        } else {
            if(!empty($videos['append_video'])) {
                $vars['videoSrc'] = $this->getParameter('qiniu_domain').'/'.array_pop($videos['append_video']);
            } else {
                $vars['videoSrc'] = $this->getParameter('qiniu_domain').'/'.$videos['v1'][0];
            }
        }
        if ($userType == User::TYPE_TEMP) {
            $vars['remark'] = json_decode($remark);
            $vars['questions'] = $orderLogic->getAskQuertionMetadata();
            return $this->render('check/checkCustomer.html.twig', $vars);
        }
        if ($orderCompanyName == Config::COMPANY_JGQC) {
            $vars['remark'] = json_decode($remark);
            $vars['questions'] = $orderLogic->getAskQuertionMetadata();
        }
        
        return $this->render('check/check.html.twig', $vars);
    }

    /**
     * 获取相同vin的记录
     * @Route("/check/vin/getReportsByVin", name="report_check_getReportsByVin")
     */
    public function ajaxGetReportsByVinAction(Request $request)
    {
        // 获取用户输入的vin
        $vin = $request->query->get('vin');

        $orders = $this->getDoctrine()->getRepository('AppBundle:Order')->findReportsByVin($vin);

        $results = [];
        $tmp = [];
        foreach ($orders as $order) {
            $report = $order->getReport();
            $tmp['orderId'] = $order->getId();
            $tmp['examedAt'] = $report->getExamedAt();
            $tmp['status'] = $report->getStatus();
            $tmp['report'] = $report->getReport();
            $results[] = $tmp;
        }

        if ($results) {
            return new JsonResponse(array('success' => true, 'results' => $results));
        } else {
            return new JsonResponse(array('success' => false, 'msg' => '无重复vin码记录'));
        }
    }

    /**
     * @Route("/ajax_get_check_step2", name="ajax_get_check_step2")
     */
    public function ajaxGetCheckStep2HtmlAction(Request $request)
    {
        $vars['vin'] = $request->get('vin');
        try {
            $vars['reportLogic'] = $this->get('ReportLogic');
            return $this->render('check/ajaxGetCheckStep2.html.twig', $vars);
        } catch (\Exception $e) {
            return new Response($e->getMessage());
        }
    }

    /**
     * 目的：利用“力洋”数据回填tab页3
     * @Route("/ajax_get_check_step3", name="ajax_get_check_step3")
     */
    public function ajaxGetCheckStep3HtmlAction(Request $request)
    {
        $ReportLogic = $this->get('ReportLogic');
        $vehicle = $ReportLogic->matchLevelID($request->get('levelID'));
        $collocate = json_decode($vehicle->getCollocate(), true);
        $prototype = array_merge($collocate, [
            '车辆类型' => $vehicle->getType(),
            '排量' => $vehicle->getDisplacement(),
            '环保标准' => $vehicle->getEmissionStandard(),
        ]);
        $filldata = $ReportLogic->backfillDataFromInit($prototype);

        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $vars['doms3'] = $mm->buildDom($mm->getMetadata4CheckStep3(), $filldata);
        return $this->render('check/ajaxGetCheckStep3.html.twig', $vars);
    }

    /**
     * @Security("has_role('ROLE_EXAMER')")
     * @Route("/doCheck/{id}", name="do_check")
     * @ParamConverter("report", class="AppBundle:Report")
     */
    public function doCheckAction(Report $report, Request $request, $id)
    {
        $form = $request->get('form');
        $stage = $request->query->get('stage');
        $type = $request->query->get('type');

        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        foreach ($mm->getMetadata4CheckArray() as $metadatas) {
            foreach ($metadatas as $metadata) {
                $reportData[$metadata->key] = $metadata->makeValue(@$form[$metadata->key]);
            }
        }

        $report = $this->get('ReportLogic')->checkReport($report, $reportData, $stage, $type);

        return new JsonResponse(['success' => true, 'stage' => $report->getStage()]);
    }

    /**
     * @Security("has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/doConfirm/{id}", name="do_confirm")
     * @ParamConverter("report", class="AppBundle:Report")
     */
    public function doConfirmAction(Request $request, Report $report)
    {
        $newReport = $request->get('form');
        $stage = $request->query->get('stage');
        $type = $request->query->get('type');
        $oldReport = $report->getPrimaryReport();          
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        foreach ($mm->getMetadata4CheckArray() as $metadatas) {
            foreach ($metadatas as $metadata) {
                $secReportData[$metadata->key] = $metadata->diffValue(@$oldReport[$metadata->key], @$newReport[$metadata->key]);
            }
        }

        $report = $this->get('ReportLogic')->confirmReport($report, $secReportData, $stage, $type);

        return new JsonResponse(['success' => true, 'stage' => $report->getStage()]);
    }


    /*----------------------------------------------------hpl复核-----------------------------------------------------*/
    /**
     * Hpl高价车复审
     * @Route("/recheck/{order_id}", name="report_recheck_edit")
     *
     */
    public function recheckEditAction($order_id)
    {
        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->find($order_id);
        $report = $order->getReport();

        $report_id = $report->getId();
        // 获取又一车评估结果的字段
        $result = isset($report->getReport()['field_result']) ? $report->getReport()['field_result']['value'] : '';

        //渲染图片需要的信息
        $orderLogic = $this->get("OrderLogic");
        list($metadatas, $append_metadata, $groups) = $orderLogic->getMetadatas(true, $getGroups = true);

        //渲染表单字段需要的信息
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $checkStep5 = $mm->getMetadata4CheckStep5();

        // 去掉options中的onlyExamer
        foreach ($checkStep5 as $k => $v) {
            if (!empty($v->options['onlyExamer'])) {
                unset($checkStep5[$k]);
            }
        }
        // 去掉options中的readonly,全部可编辑
        foreach ($checkStep5 as $k => $v) {
            if (isset($checkStep5[$k]->options['readonly'])) {
                unset($checkStep5[$k]->options['readonly']);
            }
        }

        $doms = $mm->buildDom($checkStep5, $report->getReport());
        $vars = [
            'order' => $order,
            'metadatas' => $metadatas,
            'append_metadata' => $append_metadata,
            'groups' => $groups,
            'id' => $report_id,
            'doms' => $doms,
            'result' => $result,
        ];

        return $this->render('check/recheck_edit.html.twig', $vars);
    }

    /**
     * 保存高价车复审表单数据
     * @Route("/recheck/submit/{id}", name="report_recheck_submit")
     *
     */
    public function recheckSubmitAction(Request $request, Report $report)
    {
        $data = $request->request->get('form');

        if (!$data or 0 !== $report->getStatus()) {
            throw $this->createAccessDeniedException('订单数据异常，请核实！');
        }

        //获取退回原因
        $backReason = $data['reason'] ? $data['reason'] : '';
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $oldReport = $report->getPrimaryReport();
        $secReport = $report->getSecReport();
        $reportData = array();
        if (!empty($secReport)) {
            foreach ($mm->getMetadata4CheckStep5() as $metadata) {
                if (isset($data[$metadata->key])) {
                    $reportData[$metadata->key] = $metadata->diffValue(@$oldReport[$metadata->key], @$data[$metadata->key]);
                }
            }
            $report->setSecReport(array_merge($secReport, $reportData));
        } else {
            foreach ($mm->getMetadata4CheckStep5() as $metadata) {
                if (isset($data[$metadata->key])) {
                    $reportData[$metadata->key] = $metadata->makeValue($data[$metadata->key]);
                }
            }
            $report->setReport(array_merge($oldReport, $reportData));
        }

        if (!$reportData) {
            throw $this->createAccessDeniedException('订单数据有异常，请核实！');
        }

        $this->get('ReportLogic')->updateRecheck($report, $backReason);

        return $this->redirectToRoute('order_recheck_list');
    }

    /**
     * @Route("/prepare/{id}", name="prepare_check")
     * @ParamConverter("report", class="AppBundle:Report")
     */
    public function prepareCheckAction(Report $report)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $data = $report->getReport();

        foreach ($mm->getMetadata4CheckArray() as $metadatas) {
            foreach ($metadatas as $metadata) {
                if (isset($metadata->options['required']) && $metadata->options['required'] === false ) {
                    continue;
                }

                $key = $metadata->key;
                //过户次数可能为0
                if (isset($data[$key]) && (empty($data[$key]['value']) && $data[$key]['value'] !== '0')) {
                    return new JsonResponse(['success' => false, 'message' => $metadata->display . '不能为空！']);
                }
            }
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * @Route("/erppass/{id}", name="report_erppass")
     * @Method("POST")
     * @ParamConverter("report", class="AppBundle:Report")
     */
    public function erppassAction(Report $report)
    {
        $this->get('ReportLogic')->pass2ERPReport($report);
        return new JsonResponse(['success' => true]);
    }

    /**
     * @Security("has_role('ROLE_EXAMER_RECHECK') or has_role('ROLE_EXAMER')")
     * @Route("/finish/{id}", name="finish_check")
     */
    public function finishCheckAction(Report $report, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($report->getReport()['field_result']['value'] == '评估通过') {
            $report = $this->get('ReportLogic')->passReport($report);
        } else {
            $report = $this->get('ReportLogic')->refuseReport($report);
        }

        if ($this->isGranted('ROLE_EXAMER_MANAGER')) {
            return $this->redirect($this->generateUrl('order_task_list'));
        } else if($this->isGranted('ROLE_EXAMER_RECHECK')) {
            return $this->redirect($this->generateUrl('order_getconfirm'));
        } else {
            return $this->redirect($this->generateUrl('order_task'));
        }
    }

    /**
     * @Security("has_role('ROLE_EXAMER')")
     * @Route("/primaryFinish/{id}", name="primary_finish_check")
     */
    public function primaryFinishCheckAction(Report $report, Request $request)
    {
        $event = new OrderPrimaryExamEvent($report);
        $this->get('event_dispatcher')->dispatch(HplEvents::ORDER_PRIMARY_EXAM, $event);
        if ($this->isGranted('ROLE_EXAMER_MANAGER')) {
            return $this->redirect($this->generateUrl('order_task_list'));
        } else {
            return $this->redirect($this->generateUrl('order_task'));
        }
    }

    /** 
     * @Security("has_role('ROLE_EXAMER') or has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/back/{id}", name="back")
     */
    public function backAction(Report $report)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $domArray = $mm->buildDom($mm->getMetadata4BackReason(true));
        $vars['id'] = $report->getId();
        $order = $this->getDoctrine()->getRepository('AppBundle:Report')->findOrder($vars['id']);
        $companyName = $order->getCompany()->getCompany();
        //videometa需要按照公司策略配置
        $mn = $bf->getMetadataManager($companyName);
        $domVideoArray = $mn->buildDom($mn->getMetadata4BackReasonVideo(true));

        $vars['order'] = $order;
        $vars['domArray'] = $domArray;
        $vars['domVideoArray'] = $domVideoArray;
        $vars['isExamerManager'] = $this->isGranted('ROLE_EXAMER_MANAGER') ? true : false;

        return $this->render('check/back.html.twig', $vars);
    }

    /**
     * @Security("has_role('ROLE_EXAMER') or has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/doBack/{id}", name="do_back")
     */
    public function doBackAction(Report $report, Request $request)
    {
        $form = $request->request->get('form');
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        foreach ($mm->getMetadata4BackReason() as $metadata) {
            if (!empty($form[$metadata->key])) {
                $backData[$metadata->key] = $metadata->makeValue($form[$metadata->key]);
            }
        }

        foreach ($mm->getMetadata4BackReasonVideo() as $metadatas) {
            if (!empty($form[$metadatas->key])) {
                $backData[$metadatas->key] = $metadata->makeValue($form[$metadatas->key]);
            }
        }
        $em = $this->getDoctrine()->getManager();
        $orderBack = $this->get('ReportLogic')->createOrderBack($report, $backData, $form['mainReason']);

        if ($orderBack) {
            $order = $em->getRepository('AppBundle:Report')->findOrder($report->getId());
            $order->setLocked(false);
            $order->setLockOwner(null);
            $em->flush();
        }

        return new JsonResponse(['success' => $orderBack ? true : false]);
    }

    /**
     * 估价和获取车系车型
     * @Route("/ajax_eval_price", name="eval_price")
     */
    public function ajaxEvalPriceAction(Request $request)
    {
        $Chesanbai = $this->get('Chesanbai');
        $form = $request->get('evalform');
        $params = [];
        if (!empty($form['evalModel'])) {
            $formAll = $request->get('form');
            $regDate = $formAll['field_1060']['append']['radio'] == '已注册' ? $formAll['field_1060']['value'] : null;
            $regDate = (new \DateTime($regDate))->format('Y-m');
            if (false === $field_3040 = strtotime($formAll['field_3040']['value'])) {
                $field_3040 = strtotime($formAll['field_3040']['value'] . '/01');
            }
            $makeDate = (new \DateTime())->setTimestamp($field_3040)->format('Y-m');
            $mile = ($formAll['field_3010']) / 10000;
            $color = $formAll['field_3030']['value'];
            $city = $this->retrieveCityIdViaCityName($form['evalCity']);
            if (!$city) {
                return new JsonResponse(['success' => false, 'result' => ['error_msg' => '没有找到该城市']]);
            }
            $state = [$form['surface'], $form['interior'], $form['work_state']];
            $result['evalprice'] = $Chesanbai->getUsedCarPriceAnalysis($form['evalModel'], $regDate, $makeDate, $mile, $color, $city, $state);
            $result['newprice'] = $Chesanbai->getCarModelInfo($form['evalModel']);
            if ($result['evalprice']['status'] && $result['newprice']['status']) {
                $success = true;
                // 向数据库更新车三百返回的数据
                $report = $this->getDoctrine()->getRepository('AppBundle:Report')->find($formAll['reportId']);
                $report->setCsbResults($result['evalprice']);
                $em = $this->getDoctrine()->getManager();
                $em->persist($report);
                $em->flush();

                $result['evalprice'] = $result['evalprice']['eval_prices']['b2c_price'];
                $result['newprice'] = $result['newprice']['model']['price'];
            } else {
                $success = false;
                $result['error_msg'] = @$result['evalprice']['error_msg'] . ' ' . @$result['evalprice']['error_msg'];
            }
            return new JsonResponse(['success' => $success, 'result' => $result]);
        } elseif (!empty($form['evalSeries'])) {
            $params['seriesId'] = $form['evalSeries'];
            $lists = $Chesanbai->getCarModelList($params);
            $retrieve = 'evalform[evalModel]';
        } else {
            $params['brand'] = $form['evalBrand'];
            try {
                $lists = $Chesanbai->getCarSeriesList($params);
            } catch (\Exception $e) {
                return new JsonResponse(['success' => false, 'result' => ['error_msg' => $e->getMessage()]]);
            }
            $retrieve = 'evalform[evalSeries]';
        }
        foreach ($lists as $key => $list) {
            foreach ($list as $k => $v) {
                if (in_array($k, ['model_id', 'series_id'])) {
                    $result[$key]['id'] = $v;
                }
                if (in_array($k, ['model_name', 'series_name'])) {
                    $result[$key]['name'] = $v;
                }
            }
        }
        return new JsonResponse(['success' => $result ? true : false, 'retrieve' => $retrieve, 'result' => $result]);
    }

    /**
     * 调用第一车网接口获取品牌
     * @Route("/dyc/brands", name="report_dyc_brands")
     */
    public function getBrandsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $brandsOriginal = $em->getRepository('AppBundle:Brand')->findAll();
        // dump($brandsOriginal);exit;
        foreach ($brandsOriginal as $key => $value) {
            $brands[$value->getBrandId()] = $value->getName();
        }

        return new JsonResponse($brands);
    }

    /**
     * 调用第一车网接口获取车系
     * @Route("/dyc/series/{brandId}", name="report_dyc_series")
     */
    public function getSeriesAction($brandId)
    {
        $em = $this->getDoctrine()->getManager();
        $brand = $em->getRepository('AppBundle:Brand')->findOneByBrandId($brandId);

        if ($brand) {
            // $brandId = $brand->getBrandId();
            $seriesOriginal = $this->get('app.third.dyc')->getSeries($brandId);
            $tmp = json_decode($seriesOriginal);

            //将第一车网里面返回的所有车系整理成数组
            foreach ($tmp as $k1 => $v1) {
                $carSeries = $v1->car_series;
                foreach ($carSeries as $k2 => $v2) {
                    $series[$v2->id] = $v2->name;
                }
            }
        }

        return new JsonResponse($series);
    }

    /**
     * 调用第一车网接口根据车系获取购买年份
     * @Route("/dyc/purchaseYear/{seriesId}", name="report_dyc_years")
     */
    public function getYearsAction($seriesId)
    {
        $yearsOriginal = $this->get('app.third.dyc')->getPurchaseYears($seriesId);
        $tmp = json_decode($yearsOriginal);

        foreach ($tmp as $key => $value) {
            $years[$value] = $value;
        }

        return new JsonResponse($years);
    }

    /**
     * 调用第一车网接口根据车系和购买年份获取车型
     * @Route("/dyc/models/{seriesId}/{purchaseYear}", name="report_dyc_models")
     */
    public function getModelsAction($seriesId, $purchaseYear)
    {
        $modelsOriginal = $this->get('app.third.dyc')->getModels($seriesId, $purchaseYear);

        $tmp = json_decode($modelsOriginal);

        foreach ($tmp as $key => $value) {
            $models[$value->id] = $value->full_name;
        }

        return new JsonResponse($models);
    }

    /**
     * 调用第一车网接口根据车型和购买年份获取二手价格
     * @Route("/dyc/prices/{modelId}/{purchaseYear}", name="report_dyc_prices")
     */
    public function getPricesAction($modelId, $purchaseYear)
    {
        $pricesOriginal = $this->get('app.third.dyc')->getPrices($modelId, $purchaseYear);

        return new Response($pricesOriginal);
    }

    /***********private 公用函数*****************/
    private function checkFinishReportOwner(Report $report, $status)
    {
        if ($report->getStatus() !== $status) {
            throw $this->createAccessDeniedException('检测报告状态不正确！');
        }
    }


    /**
     * 车三百城市，根据city name返回city id
     * @param $cityName
     * @return string|null
     */
    public function retrieveCityIdViaCityName($cityName)
    {
        $cityResult = $this->get('Chesanbai')->getAllCity();
        if (empty($cityResult['error_msg'])) {
            foreach ($cityResult as $city) {
                if ($cityName == $city['city_name']) {
                    return $city['city_id'];
                }
            }
        }
        return null;
    }

    /**
     * 维修报告根据来源重构数据
     * @param $originData
     * @return Array
     */
    public function maintainDataReconsitution($originData)
    {
        $jsonData = json_decode($originData);

        $decollatorBr = "<br />";

        if (!isset($jsonData)) {
            $maintain['hadReport'] = false;
        } else {
            $maintain['originType'] = $jsonData->maintain_type;
            $maintain['hadReport'] = true;
            $resultDescription = $jsonData->report->result_description;

            switch ($maintain['originType']) {
                case 1://大圣来了

                    $resultDescription = nl2br($resultDescription);

                    $resultDescriptionArr = explode($decollatorBr, $resultDescription);

                    foreach ($resultDescriptionArr as $k1 => $v1) {
                        $resultDescriptionArr[$k1] = trim($v1);
                    }

                    $itemMaintain = json_decode($jsonData->maintenance->result_content);

                    if ($itemMaintain) {
                        foreach ($itemMaintain as $k2 => $v2) {
                            if (is_string($itemMaintain[$k2]->images)) {
                                $itemMaintain[$k2]->images = explode(',', $itemMaintain[$k2]->images);
                            }
                        }
                    }

                    break;
                case 2://车鉴定
                    if (is_object($resultDescription)) {
                        $sd = "结构部件：" . ($resultDescription->sd ? '异常' : '正常');
                        $ab = "安全气囊：" . ($resultDescription->ab ? '异常' : '正常');
                        $mi = "里程表：" . ($resultDescription->mi ? '异常' : '正常');
                        $ronum = "维保次数：" . $resultDescription->ronum;
                        $mile = "最大里程：" . $resultDescription->mile . '公里';
                        $resultDescription = $sd . $decollatorBr . $ab . $decollatorBr . $mi . $decollatorBr . $ronum . $decollatorBr . $mile;
                    }

                    $resultDescriptionArr = explode($decollatorBr, $resultDescription);

                    $itemMaintain = $jsonData->maintenance;

                    $itemMaintain = array_reverse($itemMaintain);
                    if ($itemMaintain) {
                        foreach ($itemMaintain as $k3 => $v3) {
                            $itemMaintain[$k3]->content = str_replace("&nbsp;", " ", $itemMaintain[$k3]->content);
                            $itemMaintain[$k3]->material = str_replace("&nbsp;", " ", $itemMaintain[$k3]->material);

                            $decollator = "";

                            if ($itemMaintain[$k3]->content) {
                                $itemMaintain[$k3]->content = "项目：" . $itemMaintain[$k3]->content;
                            }

                            if ($itemMaintain[$k3]->material) {
                                $itemMaintain[$k3]->material = "材料：" . $itemMaintain[$k3]->material;
                            }

                            if ($itemMaintain[$k3]->content && $itemMaintain[$k3]->material) {
                                $decollator = "\n\n";
                            }

                            $itemMaintain[$k3]->contentJoin = $itemMaintain[$k3]->content . $decollator . $itemMaintain[$k3]->material;
                        }
                    }

                    break;
                default:
                    $maintain['hadReport'] = false;//多此一举
            }
        }

        if ($maintain['hadReport']) {
            $maintain['basic'] = $jsonData->basic;
            $maintain['resume'] = $resultDescriptionArr;
            $maintain['record'] = $itemMaintain;
        }

        return $maintain;
    }

    /**
     * 查询历史估价 - 根据 品牌 车系 年款 车型 搜索
     *
     * @Route("/historical",name="report_historical_appraisal")
     */
    public function HistoricalAppraisalAction(Request $request)
    {
        $title = "查询历史估价";
        $page = $request->query->getInt('page', 1);
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;
        $vars['modelId'] = $request->get('modelId', -1);//车型
        $vars['brand'] = $request->get('brand');//品牌
        $vars['series'] = $request->get('series');//车系
        $vars['model'] = $request->get('model');//车型
        $vars['year'] = $request->get('year');//年款
        $vars['type'] = $request->get('type');//类型：1查询总数 2查询所在城市记录 3不同城市记录
        $vars['city'] = $request->get('city');//获取当前城市名称
        $result = $this->get('ReportLogic')->getHistorical($vars, $page, $perPageLimit);
        if ($vars['type'] == 1) {
            return new JsonResponse(['number' => $result]);
        }
        $vars['result'] = $result;
        return $this->render('reportcheck/appraisal-historical.html.twig', [
            'vars' => $vars,
            'pagination' => $vars['result'],
            'title' => $title,
            'perPageLimit' => $perPageLimit,
        ]);
    }
}
