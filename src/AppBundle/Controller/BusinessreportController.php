<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;
use AppBundle\Business\CsvLogic;
use AppBundle\Entity\Order;
use AppBundle\Entity\Report;
use AppBundle\Entity\OrderBack;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\Config;

/**
 * Businessreport controller.
 * @Security("has_role('ROLE_USER')")
 * @Route("/business")
 */
class BusinessreportController extends Controller
{
    /**
     * @Route("/loadofficer",name="loadofficer_report")
     */
    public function loadOfficerReportAction(Request $request)
    {
        $title = "信贷员业绩";
        $user = $this->getUser();
        $vars['dateType'] = $request->query->get('vars')['dateType'];
        $vars['startDate'] = $request->query->get('vars')['startDate'];
        $vars['endDate'] = $request->query->get('vars')['endDate'];
        $vars['server'] = $_SERVER['HTTP_HOST'];//初步使用
        $vars['company'] = $request->query->get('vars')['company'];
        $vars['agency'] = $request->query->get('vars')['agency'];
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $queryresutlt = $this->get('BusinessReportLogic')
                    ->loadOfficerQuery($user, $vars['startDate'], $vars['endDate'], $vars['company'], $vars['agency']);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $queryresutlt, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );
        return $this->render('businessreport/loadofficerreport.html.twig',array(
            'pagination' => $pagination,
            'vars'  => $vars,
            'title' => $title,
            'perPageLimit' => $perPageLimit, 
        ));
    }


    /**
     * @Route("/loadofficercsv",name="loadofficercsv_report")
     */
    public function loadOfficerCsvAction(Request $request)
    {
        $title = "信贷员业绩";
        $user = $this->getUser();
        $vars['startDate'] = $request->query->get('vars')['startDate'];
        $vars['endDate'] = $request->query->get('vars')['endDate'];
        $vars['company'] = $request->query->get('vars')['company'];

        $em = $this->get('doctrine')->getManager();
        //$queryresutlt = $this->getDoctrine()->getRepository('AppBundle:Order')
        //    ->loadOfficerQuery($user, $vars['startDate'], $vars['endDate']);
        $queryresutlt = $this->get('BusinessReportLogic')
                    ->loadOfficerQuery($user, $vars['startDate'], $vars['endDate'], $vars['company']);
        $result_count =count($queryresutlt);
        $fieldViewName = [
            0 => '所在总部',
            'a' => '所属公司/区域',
            1 => '信贷员',
            2 => '提交单据总数',
            3 => '通过数',
            4 => '拒绝数',
            5 => '判定中',
            // 6 => '退回数',
        ];
        $get_result = function($queryresutlt) use(&$em) {
        gc_enable();
        $result = [];
        foreach ($queryresutlt as $k=>$order) {
            $result[$k][0] = $order['company'];
            $result[$k]['a'] = $order['agencyName'];
            $result[$k][1] = $order['name'];
            $result[$k][2] = $order['resultall'];
            $result[$k][3] = $order['resultpass'];
            $result[$k][4] = $order['resultrefused'];
            $result[$k][5] = $order['resultaction'];
            // $result[$k][6] = $order['resultback'];
        }
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $em->clear();
        gc_collect_cycles();
        return $result;
    };
    $get_resultnew = $get_result($queryresutlt);
    return $this->get('CsvLogic')->exportCSV($result_count, $get_resultnew, '信贷员业绩', $fieldViewName);
    }


    /**
     * @Route("/vehiclereport",name="vehicle_report")
     */
    public function vehicleReportAction(Request $request)
    {
        $title = "车辆详表";
        $user = $this->getUser();
        $company = $user->getAgencyRels()[0]->getCompany()->getCompany();
 
        $fieldDisplay = $this->get('app.business_factory')->getFieldPolicy($company);
        $vars['company'] = $request->query->get('vars')['company'];
        $vars['agency'] = $request->query->get('vars')['agency'];
        $vars['mixed'] = $request->query->get('vars')['mixed'];
        $vars['status'] = $request->query->get('vars')['status'];
        // 1代表当天
        $vars['dateType'] = $request->query->get('vars')['dateType'] ?: 1;

        if ($vars['dateType'] == 1) {
            $vars['startDate'] = date('Y-m-d');
            $vars['endDate'] = date('Y-m-d');
        } else {
            $vars['startDate'] = $request->query->get('vars')['startDate'];
            $vars['endDate'] = $request->query->get('vars')['endDate'];
        }

        $vars['server'] = $_SERVER['HTTP_HOST'];//初步使用
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $query = $this->get('BusinessReportLogic')
                    ->vehicleQuery($user, $vars['mixed'],$vars['status'],$vars['startDate'],$vars['endDate'], $vars['company'], $vars['agency']);
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('businessreport/vehiclereport.html.twig',array(
            'pagination' => $pagination,
            'vars'  => $vars,
            'title' => $title,
            'fieldDisplay' => $fieldDisplay,
            'perPageLimit' => $perPageLimit,
            'dateTime' => $this->get('util.dateTime'),
        ));
    }


    /**
     * @Route("/vehiclereportcsv",name="vehiclereport_csv")
     */
    public function vehicleReportCsvAction(Request $request)
    {
        $title = "车辆详表";
        $user = $this->getUser();
        $company = $user->getAgencyRels()[0]->getCompany()->getCompany();

        $fieldDisplay = $this->get('app.business_factory')->getFieldPolicy($company);

        $vars['mixed'] = $request->query->get('vars')['mixed'];
        $vars['status'] = $request->query->get('vars')['status'];
        $vars['startDate'] = $request->query->get('vars')['startDate'];
        $vars['endDate'] = $request->query->get('vars')['endDate'];
        $em = $this->get('doctrine')->getManager();
        $query = $this->get('BusinessReportLogic')
                    ->vehicleQuery($user, $vars['mixed'], $vars['status'], $vars['startDate'], $vars['endDate']);
        $paginator = new Paginator($query);
        $result_count = $paginator->count();
            $fieldViewName = [
                0 => '评估单号',
                1 => '单据状态',
                'result' => '评估结果',
                2 => '信贷员',
                'examer' => '审核师',
                3 => '所在总部',
                'k1' => '所属公司/区域',
                4 => '车牌号',
                5 => '品牌',
                6 => '车系',
                7 => '车型',
                8 => '厂牌型号',
                'vin' => 'vin码',
                9 => '排量',
                10 => '预售价格',
                11 => '出厂日期',
                12 => '登记日期',
                13 => '公里数',
                // 14 => '评估价格',
                'purchasePrice' => '评估收购价',
                'sellPrice' => '评估销售价',
                15 => '备注',
                16 => '提交时间',
                17 => '评估完成时间',
                18 => '评估时长(分钟)',
                'totalTime' => '总时长(分钟)',
                19 => '省份',
                'city' => '城市',
                20 => '查看图片',
                21 => '评估报告',
            ];


        if ($fieldDisplay['purchasePricePc'] and $fieldDisplay['sellPricePc']) {
        } elseif ($fieldDisplay['purchasePricePc']) {
            $fieldViewName['purchasePrice'] = '评估价格';
            unset($fieldViewName['sellPrice']);
        } elseif ($fieldDisplay['sellPricePc']) {
            $fieldViewName['sellPrice'] = '评估价格';
            unset($fieldViewName['purchasePrice']);
        } else {
           unset($fieldViewName['purchasePrice']);
           unset($fieldViewName['sellPrice']);
        }

        if (!$fieldDisplay['report']) {
           unset($fieldViewName[21]);
        }

        if (!$fieldDisplay['valuation']) {
           unset($fieldViewName[10]);
        }

        if (!($fieldDisplay['showExamer'] and $this->isGranted('ROLE_EXAMER'))) {
           unset($fieldViewName['examer']);
        }

        if (!$this->isGranted('ROLE_EXAMER_MANAGER')) {
            unset($fieldViewName['stageBasic']);
            unset($fieldViewName['stageModel']);
            unset($fieldViewName['stageConfig']);
            unset($fieldViewName['stageSummarize']);
            unset($fieldViewName['stagePrice']);
        }

        $get_result = function($paginator) use(&$em, $fieldDisplay) {
            gc_enable();
            $result = [];
            foreach ($paginator as $k=>$order) {
                $result[$k][0] = $order->getOrderNo();

                if ($order->getBusinessNumber()) {
                    $result[$k][0] = $result[$k][0].'('.$order->getBusinessNumber().')';
                }

                $report = $order->getReport();
                $report_report = $report->getReport();
                $reportStatus = $report->getStatus();
                if($reportStatus == 1){
                    $result[$k][1] = "通过";
                }else if($reportStatus == 2){
                    $result[$k][1] = "拒绝";
                }else{
                    $result[$k][1] = "已提交";
                }

                $evaluateResult = '';
                if (1 === $reportStatus) {
                    $evaluateResult = '通过';
                } else {
                    if (isset($report_report['field_result']['options']) && isset($report_report['field_result']['options']['textarea'])) {
                        if ($order->getReport()->getReport()['field_result']['options']['textarea']) {
                            $evaluateResult = $order->getReport()->getReport()['field_result']['options']['textarea'];
                        }
                    }
                }

                $result[$k]['result'] = $evaluateResult;
                $result[$k][2] = $order->getLoadOfficer()->getName();
                $result[$k]['examer'] = $order->getReport() ? $order->getReport()->getExamer()->getName() : '';
                $result[$k][3] = $order->getCompany()->getCompany();
                $result[$k]['k1'] = $order->getAgencyCode();
                $result[$k][4] = $order->getReport()->getReport() ? $order->getReport()->getReport()['field_1010']['value'] : "";
                $result[$k][5] = $order->getReport()->getBrand();
                $result[$k][6] = $order->getReport()->getSeries();
                $result[$k][7] = $order->getReport()->getModel();
                $result[$k][8] = $order->getReport()->getReport() ? $order->getReport()->getReport()['field_1030']['value'] : "";

                $result[$k]['vin'] = $order->getReport()->getVin();
                $result[$k][9] = $order->getReport()->
                getReport() ? $order->getReport()->getReport()['field_3020']['value'] : "";
                $result[$k][10] = $order->getValuation();
                $result[$k][11] = $order->getReport()->getReport() ? $order->getReport()->getReport()['field_3040']['value'] : "";
                $result[$k][12] = $order->getReport()->getReport() ? $order->getReport()->getReport()['field_1060']['value'] : "";

                $result[$k][13] = $order->getReport()->getReport() ? $order->getReport()->getReport()['field_3010']['value'] : "";

                // $result[$k][14] = $order->getReport()->getReport() ? $order->getReport()->getReport()['field_4010']['value'] : "";
                $result[$k]['purchasePrice'] = $order->getReport()->getReport() ? $order->getReport()->getReport()['field_4010']['value'] : "";
                if ($order->getReport()->getReport()) {
                    $reportData = $order->getReport()->getReport();
                    if (isset($reportData['field_4012']) && $reportData['field_4012']['value']) {
                        $result[$k]['sellPrice'] = $reportData['field_4012']['value'];
                    } else {
                        $result[$k]['sellPrice'] = '';
                    }
                } else {
                    $result[$k]['sellPrice'] = '';
                }


                $result[$k][15] = $order->getRemark();
                $result[$k][16] = $order->getSubmitedAt()->format("Y-m-d H:i:s");
                $result[$k][17] = $order->getReport()->getExamedAt()?$order->getReport()->getExamedAt()->format("Y-m-d H:i:s"):"";
                $timeresult = 0;
                $result[$k][18] = "时间无";
                if($order->getReport()->getExamedAt() && $order->getSubmitedAt()){
                    $timeresult = $order->getReport()->getExamedAt()->getTimestamp() - $order->getReport()->getCreatedAt()->getTimestamp();
                    $result[$k][18] = number_format(($order->getReport()->getExamedAt()->getTimestamp() - $order->getReport()->getCreatedAt()->getTimestamp())/60);
                }

                $result[$k]['totalTime'] = "时间无";
                if($order->getReport()->getExamedAt() && $order->getSubmitedAt()){
                    $result[$k]['totalTime'] = $this->get('util.dateTime')->calculateDiffTime($order->getSubmitedAt(), $order->getReport()->getExamedAt());
                }

                $result[$k][19] = $order->getLoadOfficer()->getProvince() ? $order->getLoadOfficer()->getProvince()->getName() : '';
                $result[$k]['city'] = $order->getLoadOfficer()->getCity() ? $order->getLoadOfficer()->getCity()->getName() : '';
                $result[$k][20] = $this->get('router')->generate("order_show", ["id"=>$order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
                
                $result[$k][21] = $this->get('router')->generate("pdfreport", ["orderid"=>$order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);


                if ($fieldDisplay['purchasePricePc'] and $fieldDisplay['sellPricePc']) {
                } elseif ($fieldDisplay['purchasePricePc']) {
                    unset($result[$k]['sellPrice']);
                } elseif ($fieldDisplay['sellPricePc']) {
                    unset($result[$k]['purchasePrice']);
                } else {
                    unset($result[$k]['purchasePrice']);
                    unset($result[$k]['sellPrice']);
                }

                if (!$fieldDisplay['report']) {
                    unset($result[$k][21]);
                }

                if (!$fieldDisplay['valuation']) {
                    unset($result[$k][10]);
                }

                if (!($fieldDisplay['showExamer'] and $this->isGranted('ROLE_EXAMER'))) {
                   unset($result[$k]['examer']);
                }
            }

            $em->getConnection()->getConfiguration()->setSQLLogger(null);
            $em->clear();
            gc_collect_cycles();

            return $result;
        };

        return $this->get('CsvLogic')->queryExportCSV($result_count, $query, $get_result, '车辆详表', $fieldViewName);
    }

}
