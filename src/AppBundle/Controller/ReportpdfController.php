<?php

/*
 * Copyright 2011 Piotr Śliwa <peter.pl7@gmail.com>
 *
 * License information is in LICENSE file
 */

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ReportpdfController extends Controller
{
    /**
     * 因业务调整将报告拆分(初审报告,复审报告),此处显示复审报告
     * @Route("/pdfreport/{orderid}/{_format}/{recheck}", name="pdfreport",
     *     defaults={"_format": "html", "recheck": 0},
     *     requirements={
     *         "_format": "pdf|html|image"
     *     }
     * )
     */
    public function reportAction($orderid, $_format, $recheck, Request $request)
    {
        $data = $this->get('ShowPdfHtmlLogic')->getJsonReport($orderid);
        if(empty($data)){
            throw new \Exception("数据不能为空");
        }

        // 如果获取到的出厂日期没有精确到日，自动精确到1号
        if (strlen($data['field_3040']['value']) <= 7 && strlen($data['field_3040']['value']) > 0 ) {
            $data['field_3040']['value'] = $data['field_3040']['value'].'/01';
        }

        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->find($orderid);
        $report = $order->getReport();
        $company = $order->getCompany()->getCompany();
        $data['reportPrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['reportPrice'];
        $data['reportPriceTrend'] = $this->get('app.business_factory')->getFieldPolicy($company)['reportPriceTrend'];
        $data['maintain'] = $this->get('app.business_factory')->getFieldPolicy($company)['maintain'];
        $data['purchasePrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['purchasePricePc'];
        $data['sellPrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['sellPricePc'];
        $data['futurePrice'] = $this->get('app.business_factory')->getFieldPolicy($company, $order->getAgencyName())['futurePricePc'];
        
        $data['maintain_id'] = $report->getMaintain();
        $data['report_id'] = $report->getId();
        $data['examedAt'] = $report->getExamedAt();
        $data['averagePrice'] = 0;
        $data['biddingCount'] = 0;
        $data['maintainData'] = $this->get('MaintainLogic')->getMaintainData($report);
        $data['insuranceData'] = $this->get('InsuranceLogic')->getInsuranceData($order);

        $reportData = $report->getReport();
        $data['historyPrices'] = isset($reportData['field_2061']) && $reportData['field_2061']['value'] ? json_decode($reportData['field_2061']['value'], true) : '';
        $data['displayType'] = 'pcHtml';
        $data['carCity'] = $order->getCarCity();
        $data['accidentImg'] = isset($reportData['field_4140']) && $report->getReport()['field_4140']['value'] && isset($report->getReport()['field_2071']) && $report->getReport()['field_2071']['value'] ? explode(',', $report->getReport()['field_2071']['value']) : ''; 

        if($recheck==1){
            $data['recheck'] = $recheck;
        }

        if ($_format == "pdf") {
            $data['displayType'] = 'pcPdf';
            $html = $this->renderView('reportcheck/appraisal-report.html.twig', $data);
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                200,
                ['Content-Type' => 'application/pdf']
            );
        }

        if ($_format == "image") {
            $html = $this->renderView('reportcheck/appraisal-report.html.twig', $data);
            return new Response(
                $this->get('knp_snappy.image')->getOutputFromHtml($html),
                200,
                [
                    'Content-Type'          => 'image/jpg',
                    'Content-Disposition'   => 'filename="report.png"'
                ]
            );
        }

        return $this->render('reportcheck/appraisal-report.html.twig', $data);
    }

    /**
     * 因业务调整将报告拆分(初审报告,复审报告),此处显示初审报告
     * @Route("/pdfreport_pri/{orderid}/{_format}/{recheck}", name="pdfreport_pri",
     *     defaults={"_format": "html", "recheck": 0},
     *     requirements={
     *         "_format": "pdf|html|image"
     *     }
     * )
     */
    public function reportPrimaryAction($orderid, $_format, $recheck, Request $request)
    {
        $data = $this->get('ShowPdfHtmlLogic')->getJsonPrimaryReport($orderid);
        if(empty($data)){
            throw new \Exception("数据不能为空");
        }

        // 如果获取到的出厂日期没有精确到日，自动精确到1号
        if (strlen($data['field_3040']['value']) <= 7 && strlen($data['field_3040']['value']) > 0 ) {
            $data['field_3040']['value'] = $data['field_3040']['value'].'/01';
        }

        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->find($orderid);
        $report = $order->getReport();
        $company = $order->getCompany()->getCompany();
        $data['reportPrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['reportPrice'];
        $data['reportPriceTrend'] = $this->get('app.business_factory')->getFieldPolicy($company)['reportPriceTrend'];
        $data['maintain'] = $this->get('app.business_factory')->getFieldPolicy($company)['maintain'];
        $data['purchasePrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['purchasePricePc'];
        $data['sellPrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['sellPricePc'];
        $data['futurePrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['futurePricePc'];
        
        $data['maintain_id'] = $report->getMaintain();
        $data['report_id'] = $report->getId();
        $data['examedAt'] = $report->getExamedAt();
        $data['averagePrice'] = 0;
        $data['biddingCount'] = 0;
        $data['maintainData'] = $this->get('MaintainLogic')->getMaintainData($report);
        $data['insuranceData'] = $this->get('InsuranceLogic')->getInsuranceData($order);

        $reportData = $report->getPrimaryReport();
        $data['historyPrices'] = isset($reportData['field_2061']) && $reportData['field_2061']['value'] ? json_decode($reportData['field_2061']['value'], true) : '';
        $data['displayType'] = 'pcHtml';
        $data['carCity'] = $order->getCarCity();
        $data['accidentImg'] = isset($reportData['field_4140']) && $report->getReport()['field_4140']['value'] && isset($report->getReport()['field_2071']) && $report->getReport()['field_2071']['value'] ? explode(',', $report->getReport()['field_2071']['value']) : ''; 

        if($recheck==1){
            $data['recheck'] = $recheck;
        }

        if ($_format == "pdf") {
            $data['displayType'] = 'pcPdf';
            $html = $this->renderView('reportcheck/appraisal-report.html.twig', $data);
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                200,
                ['Content-Type' => 'application/pdf']
            );
        }

        if ($_format == "image") {
            $html = $this->renderView('reportcheck/appraisal-report.html.twig', $data);
            return new Response(
                $this->get('knp_snappy.image')->getOutputFromHtml($html),
                200,
                [
                    'Content-Type'          => 'image/jpg',
                    'Content-Disposition'   => 'filename="report.png"'
                ]
            );
        }

        return $this->render('reportcheck/appraisal-report.html.twig', $data);
    }

    /**
     * app 客户端 报告(代码和上面的代码基本一样，只不过没有pdf)
     * @Route("/appreport/{orderid}", name="app_report")
     */
    public function appreportAction($orderid, Request $request)
    {
        $data = $this->get('ShowPdfHtmlLogic')->getJsonReport($orderid);
        if(empty($data)){
            throw new \Exception("数据不能为空");
        }

        // 如果获取到的出厂日期没有精确到日，自动精确到1号
        if (strlen($data['field_3040']['value']) <= 7 && strlen($data['field_3040']['value']) > 0 ) {
            $data['field_3040']['value'] = $data['field_3040']['value'].'/01';
        }

        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->find($orderid);
        $report = $order->getReport();
        $company = $order->getCompany()->getCompany();
        $data['reportPrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['reportPrice'];
        $data['reportPriceTrend'] = $this->get('app.business_factory')->getFieldPolicy($company)['reportPriceTrend'];
        $data['maintain'] = $this->get('app.business_factory')->getFieldPolicy($company)['maintain'];
        $data['purchasePrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['purchasePriceApp'];
        $data['sellPrice'] = $this->get('app.business_factory')->getFieldPolicy($company)['sellPriceApp'];
        $data['futurePrice'] = $this->get('app.business_factory')->getFieldPolicy($company, $order->getAgencyName())['futurePriceApp'];
        
        $data['maintain_id'] = $report->getMaintain();
        $data['report_id'] = $report->getId();
        $data['examedAt'] = $report->getExamedAt();
        $data['averagePrice'] = 0;
        $data['biddingCount'] = 0;
        $data['maintainData'] = $this->get('MaintainLogic')->getMaintainData($report);
        $data['insuranceData'] = $this->get('InsuranceLogic')->getInsuranceData($order);

        $reportData = $report->getReport();
        $data['historyPrices'] = isset($reportData['field_2061']) && $reportData['field_2061']['value'] ? json_decode($reportData['field_2061']['value'], true) : '';
        $data['displayType'] = 'appHtml';
        $data['carCity'] = $order->getCarCity();
        $data['accidentImg'] = isset($reportData['field_4140']) && $report->getReport()['field_4140']['value'] && isset($report->getReport()['field_2071']) && $report->getReport()['field_2071']['value'] ? explode(',', $report->getReport()['field_2071']['value']) : ''; 

        return $this->render('reportcheck/appraisal-report.html.twig', $data);
    }

    /**
     * ajax获取报告差异
     * @Route("/pdfreport_diff/{orderid}", name="pdfreport_diff")
     */
    public function reportDiffAction($orderid, Request $request)
    {
        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->find($orderid);
        $report = $order->getReport();
        $data = $this->get('ReportLogic')->getDiffReport($report->getSecReport());
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();

        foreach ($mm->getMetadata4CheckArray() as $metadatas) {
            foreach ($metadatas as $metadata) {
                if(!empty($data[$metadata->key])) {
                    $data[$metadata->key]['display'] = $metadata->display;
                }  
            }
        }

        return new JsonResponse(array('success' => true, 'message' => '授权成功', 'table' => $data));
    }
}
