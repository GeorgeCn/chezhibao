<?php

namespace AppBundle\BusinessExtend\Hthy;

use AppBundle\BusinessExtend\SystemSyncBaseData;

class SyncData extends SystemSyncBaseData
{
    public function __construct()
    {
    }

    public function systemSyncNotice($order, $company, $container)
    {
        $this->container = $container;
        $config = $this->getRepo('AppBundle:Config')->findoneby(['company' => $company]);
        if(!$config) {
            $this->container->get('logger')->error('companyNotice:'.$company.'is empty');
            return false;
        }
        $url = $this->systemSyncCompanyUrl($config);
        if(!$url) {
            $this->container->get('logger')->error('companyNotice:'.$company.' url is empty');
            return false;
        }
        $report = $order->getReport();
        if(!$report) {
            $this->container->get('logger')->error('companyNotice:'.$company.' report is  empty');
            return false;
        }
        $assessInfo = $report->getStatus() == 1 ? 'Y' : 'N';
        $msg = [
            'reqHead' => [
                'transCode' => 'escInfoResult'
            ],
            'reqBody' => [
                'bookingCode' => $order->getBusinessNumber(),
                'pgdh' => $order->getOrderNo(),
                'assessInfo' => $assessInfo
            ]
        ];
        $headers = [
            'transCode:escInfoResult',
            'content-type:text/html',
        ];
        $curlHelper = $this->container->get('util.curl_helper');
        $port = $this->container->getParameter('hthyNumberPort');
        $result = $curlHelper->post($url, json_encode($msg), $headers, $port);
        $result = urldecode($result);
        $ret = json_decode($result, true);
        $status = false;
        if($ret && $ret['rspBody']['result'] == 'Y') {
            $status = true;
        } else {
            $this->container->get('logger')->error('companyNotice:'.$result);
        }

        return $status == true ? true : false;
    }

    public function validateNumber($number, $container)
    {
        $this->container = $container;

        //开发环境不需要校验
        $validateBusinessNumberSwitch = $this->container->getParameter('validateBusinessNumberSwitch');
        if ($validateBusinessNumberSwitch == false) {
            return true;
        }

        $msg = [
            'reqHead' => [
                'transCode' => 'checkasqdh'
            ],
            'reqBody' => [
                'bookingCode' => $number
            ]
        ];
        $headers = [
            'transCode:checkasqdh',
            'content-type:text/html',
        ];
        $url = $this->container->getParameter('hthyNumberUrl');
        $port = $this->container->getParameter('hthyNumberPort');
        $curlHelper = $this->container->get('util.curl_helper');
        $result = $curlHelper->post($url, json_encode($msg), $headers, $port);
        $result = urldecode($result);
        $ret = json_decode($result, true);
        if($ret && $ret['rspBody']['result'] == 'Y') {
            return true;
        }
        $this->container->get('logger')->error('validateNumber:'.$result);
        return false;
    }
}
