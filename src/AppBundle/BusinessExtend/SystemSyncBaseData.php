<?php
namespace AppBundle\BusinessExtend;

use AppBundle\Traits\ContainerAwareTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\Report;

class SystemSyncBaseData
{
    use ContainerAwareTrait;

    public function __construct()
    {
    }

    public function getOrder($company, $orderNo, $number)
    {
        $order = $this->getRepo('AppBundle:Order')->findCompanyOrder($company, $orderNo);
        if(empty($order)) {
            return false;
        }
        if($number && $order->getBusinessNumber() != $number) {
            return false;
        }
        return $order;
    }

    public function systemSyncBaseInfo($company, $orderNo, $container, $number = '')
    {
        $this->container = $container;
        $order = $this->getOrder($company, $orderNo, $number);
        if(!$order) {
            return [];
        }
        $report = $order->getReport();

        $carInfo['pgdh'] = $order->getOrderNo();//评估单号
        $carInfo['khdm'] = $order->getAgencyCode();//供应商代码(简称) 
        $carInfo['khmc'] = $order->getAgencyName();//供应商名称
        $carInfo['cxdm'] = $report->getReport()['field_1030']['value']; //车型代码(厂牌型号)?
        $carInfo['cjhm'] = $report->getVin();              //车架号
        $carInfo['fdjh'] = $report->getReport()['field_1050']['value']; //发动机号
        $carInfo['cphm'] = $report->getReport()['field_1010']['value']; //车牌号码
        $carInfo['cllx'] = $report->getReport()['field_3060']['value']; //车辆类型
        $carInfo['gl'] = $report->getReport()['field_3080']['value']; //功率
        $carInfo['pl'] = $report->getReport()['field_3020']['value']; //排量
        $carInfo['hbbz'] = $report->getReport()['field_3090']['value']; //环保标准
        $carInfo['syxz'] = $report->getReport()['field_1020']['value']; //使用性质
        $carInfo['pp'] = $report->getReport()['field_2010']['value']; //品牌
        $carInfo['px'] = $report->getReport()['field_2020']['value']; //车系
        $carInfo['cx'] = $report->getReport()['field_2030']['value']; //车型

        $ccrq = $report->getReport()['field_3040']['value']; //出厂日期
        $carInfo['ccrq'] = $ccrq;
        // 如果获取到的出厂日期没有精确到日，自动精确到1号
        if (strlen($ccrq) <= 7 && strlen($ccrq) > 0) {
            $carInfo['ccrq'] = $ccrq.'/01';
        }

        $carInfo['djrq'] = $report->getReport()['field_1060']['value'];     //登记日期
        $carInfo['nsyxq'] = $report->getReport()['field_1070']['value'];    //年审有效期
        $carInfo['xslc'] = $report->getReport()['field_3010']['value'] ;    //行驶里程
        $carInfo['zw'] = $report->getReport()['field_3050']['value'];       //座位
        $carInfo['bsxs'] = $report->getReport()['field_3100']['value'];     //变速形式
        $carInfo['cmxs'] = $report->getReport()['field_3110']['value'];     //车门型式
        $carInfo['cdfs'] = $report->getReport()['field_3120']['value'];     //传动方式 
        $carInfo['gyxt'] = $report->getReport()['field_3070']['value'];     //供油系统
        $carInfo['jrfs'] = $report->getReport()['field_3130']['value'];     //进气方式
        $carInfo['pzqt'] = '';                                              //配置其他
        $carInfo['tspbsm'] = '';                                            //特殊配置说明
        $carInfo['tscgywsm'] = '';                                          //特使车管业务说明
        $carInfo['bz'] = $order->getRemark();                               //备注(采集员备注处)
        $carInfo['zt'] = $report->getStatus() === Report::STATUS_PASS ? 1 : 0; //状态1通过，0拒绝
        $carInfo['tjrq'] = $order->getSubmitedAt()->format('Y-m-d H:i:s');  //提交日期 
        $carInfo['cyhsm'] = '';                                             //差异化说明
        $carInfo['ys'] = $report->getReport()['field_3030']['value'];       //颜色

        if ($report->getReport()['field_4140']['value']) {
            $carInfo['ishsc'] = in_array("火烧车", @$report->getReport()['field_4140']['value']) ? "Y" : "N";                                                                //是否火烧车
            $carInfo['ispsc'] = in_array("泡水车", @$report->getReport()['field_4140']['value']) ? "Y" : "N";                                                                //是否泡水车
            $carInfo['issgc'] = in_array("事故车", @$report->getReport()['field_4140']['value']) ? "Y" : "N";  
        } else {
            $carInfo['ishsc'] = "N";                                                                //是否火烧车
            $carInfo['ispsc'] = "N";                                                                //是否泡水车
            $carInfo['issgc'] = "N";  
        }
                                                              //是否事故车
        $carInfo['clsyxz'] = '';                                            //使用性质
        $carInfo['zdrmc'] = $order->getLoadOfficer()->getName();            //采集员名称
        $carInfo['ckpg' ] = $report->getReport()['field_4150']['value'];    //车况评估

        return $carInfo;
    }

    public function systemSyncPriceInfo($company, $orderNo, $container, $number = '')
    {
        $this->container = $container;
        $order = $this->getOrder($company, $orderNo, $number);
        if(!$order) {
            return [];
        }
        $report = $order->getReport();

        $carPrice['pgdh'] = $order->getOrderNo();                                   //评估单号
        $carPrice['xcj'] = $report->getReport()['field_4020']['value'];             //新车价
        $carPrice['pgjg'] = $report->getReport()['field_4012']['value'];            //评估价格(销售价)
        $carPrice['pgjg_sgj'] = $report->getReport()['field_4010']['value'];        //评估价格(收购价)
        $carPrice['pgyxq'] = '15';                                                  //评估有效期
        $carPrice['pgdw'] = '上海麦拉汽车服务有限公司';                             //评估单位
        $carPrice['shrq'] = $report->getExamedAt() ? $report->getExamedAt()->format('Y-m-d H:i:s') : '';          //审核日期

        return $carPrice;
    }

    public function systemSyncPicturesInfo($company, $orderNo, $metaDataManager, $container, $number = '')
    {
        $this->container = $container;
        $order = $this->getOrder($company, $orderNo, $number);
        if(!$order) {
            return [];
        }
        $pictures = $order->getPictures();

        $archive['登记证'] = isset($pictures['k1']) ? $this->getImageUrl($pictures['k1']) : [];
        $archive['铭牌'] = isset($pictures['k22']) ? $this->getImageUrl($pictures['k22']) : [];
        $archive['补充照片'] = [];

        $metaDatas = $metaDataManager->getMetadata4Order();
        $metaKeys = array_column($metaDatas, 'key');
        foreach ($pictures as $pk => $picture) {
            if(in_array($pk, ['k1', 'k22'])) {
                continue;
            }
            $arrKey = '';
            if(in_array('append', $metaKeys)) {
                $arrKey = strpos($pk, 'append') !== false ? '补充照片' : '车辆照片';
            }
            if($arrKey) {
                foreach ($pictures[$pk] as $k => $url) {
                    $archive[$arrKey][] = $this->getImageUrl($url);
                }
            }
        }
        $archive['车体骨架1'] = '';
        $archive['pdf'] = $this->generateUrl('pdfreport', array('orderid'=> $order->getId(), '_format' => 'pdf'), UrlGeneratorInterface::ABSOLUTE_URL);
        return $archive;
    }

    /**
     * 检查公司配置里面是否需要通知对方公司
     * 
     */
    public function checkCompanyNoticeEnable($companyName = null)
    {
        $url = '';
        //首先判断parameter.yml里是否开启了通知的开关
        $switch = $this->getParameter('notice_company');

        if (!$switch) {
            return $url;
        }

        $companyConfig = $this->getRepo('AppBundle:Config')->findOneByCompany($companyName);

        if ($companyConfig) {
            if (isset($companyConfig->getParameter()['enabled']) && $companyConfig->getParameter()['enabled']) {
                $url = $companyConfig->getParameter()['k1'];
            }
        }

        return $url;
    }

    /*
    * 等老海通不用时可以删掉该方法
    */
    protected function systemSyncCompanyUrl($config)
    {
        //首先判断parameter.yml里是否开启了通知的开关
        $switch = $this->container->getParameter('notice_company');
        if (!$switch) {
            return false;
        }
        if (isset($config->getParameter()['enabled']) && $config->getParameter()['enabled']) {
            return $config->getParameter()['k1'];
        } else {
            return false;
        }
    }

    public function validateNumber($number, $container)
    {
        $this->container = $container;
        $validateBusinessNumberSwitch = $this->getParameter('validateBusinessNumberSwitch');
        if ($validateBusinessNumberSwitch === false) {
            return true;
        }

        $order = $this->getRepo('AppBundle:Order')->findOneByBusinessNumber($number);
        $companyConfig = $order->getCompany();
        if ($companyConfig) {
            $url = $companyConfig->getParameter()['k2'];
            if (!$url) {
                return true;
            }
        } else {
            return true;
        }

        $key = $companyConfig->getCompanyKey();
        $secret = $companyConfig->getCompanySerect();
        $timestamp = time();

        $sign = $this->get('util.systemapisign')->enSign(
            [
                'key' => $key,
                'number' => $number,
                'timestamp' => $timestamp,
                'secret' => $secret,
            ]
        );

        $parameters = ['timestamp' => $timestamp, 'key' => $key, 'sign' => $sign, 'number' => $number];
        $result = $this->get('util.curl_helper')->get($url.'?'.http_build_query($parameters));

        if ($result){
            if ($result['success'] == true) {
                return true;
            } else {
                $this->get('logger')->error('validateNumber:'.json_encode($result));

                return false;
            }
        } else {
            $this->get('logger')->error('validateNumber:'.json_encode($result));

            return false;
        }
    }

    public function getImageUrl($imgName)
    {
        $imgDn = $this->container->getParameter('qiniu_domain');
        $imgs;
        if(is_array($imgName)) {
            foreach ($imgName as $k => $name) {
                $imgs[] = $imgDn.'/'.$name;
            }
        } else {
            $imgs = $imgDn.'/'.$imgName;
        }
        return $imgs;
    }

    public function systemSyncNotice($order, $company, $container)
    {
        $this->container = $container;
        $config = $this->getRepo('AppBundle:Config')->findoneby(['company' => $company]);
        if(!$config) {
            return false;
        }

        $report = $order->getReport();
        if(!$report) {
            return false;
        }

        $url = $this->checkCompanyNoticeEnable($order->getCompany()->getCompany());
        if(!$url) {
            return false;
        }

        $orderNo = $order->getOrderNo();
        $number = $order->getBusinessNumber();
        $key = $order->getCompany()->getCompanyKey();
        $secret = $order->getCompany()->getCompanySerect();
        $timestamp = time();

        $sign = $this->get('util.systemapisign')->enSign(
            [
                'key' => $key,
                'number' => $number,
                'orderNo' => $orderNo,
                'timestamp' => $timestamp,
                'secret' => $secret,
            ]
        );
        $parameters = ['timestamp' => $timestamp, 'key' => $key, 'sign' => $sign, 'orderNo' => $orderNo, 'number' => $number];
        $newUrl = $url.'?'.http_build_query($parameters);
        $ret = $this->get('util.curl_helper')->get($url, $parameters);

        echo $newUrl."\n";
        $status = false;
        if ($ret['success'] == true){
            $status = true;
        } else {
            $this->get('logger')->error('companyNotice:'.json_encode($ret));
        }

        return $status == true ? true : false;
    }

    public function getAllBrand($container)
    {
        $this->container = $container;
        $uri = '/series/allbrands';
        $ret = $this->chexiHttpRequest($uri);
        if($ret && $ret['errno'] == 0) {
            $info = [];
            foreach ($ret['data']['list'] as $k => $v) {
                $info[$v[1]] = ['brandid' => $v[1], 'name' => $v[0]];
            }
            ksort($info);
            return array_values($info);
        }
        return [];
    }

    public function getSeriesByBrand($container, $brandid)
    {
        $this->container = $container;
        $uri = '/series/getbybrandid?brandid='.$brandid;
        $ret = $this->chexiHttpRequest($uri);
        if($ret && $ret['errno'] == 0) {
            $info = [];
            foreach ($ret['data']['list'] as $k => $v) {
                $info[] = ['seriesid' => $v[1], 'name' => $v[0]];
            }
            return $info;
        }
        return [];
    }

    public function getYearBySeries($container, $seriesid)
    {
        $this->container = $container;
        $uri = '/series/getniankuan?seriesid='.$seriesid;
        $ret = $this->chexiHttpRequest($uri);
        if($ret && $ret['errno'] == 0) {
            $info = $ret['data']['list']['allyears'];
            foreach ($info as $k => $v) {
                $info[$k] = ['year' => $v];
            }
            return $info;
        }
        return [];
    }

    public function getModelByYearAndSeries($container, $year, $seriesid)
    {
        $this->container = $container;
        $uri = '/model/search?seriesid='.$seriesid.'&niankuan='.$year;
        $ret = $this->chexiHttpRequest($uri);
        if($ret && $ret['errno'] == 0) {
            $info = [];
            foreach ($ret['data']['list'] as $k => $v) {
                $info[] = ['modelid' => $v[2], 'name' => $v[0]];
            }
            return $info;
        }
        return [];
    }

    private function chexiHttpRequest($uri)
    {
        $url = $this->container->getParameter('chexiUrl').$uri;
        $port = $this->container->getParameter('chexiPort');
        $curlHelper = $this->container->get('util.curl_helper');
        $result = $curlHelper->post($url, [], [], $port);
        return !is_array($result) ? json_decode($result, true) : $result;
    }
}
