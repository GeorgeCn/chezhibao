<?php

namespace YYC\FoundationBundle\Third;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * 查博士
 */
class Cbs
{

    const callback = 'http://1ad068c3.ngrok.io/cbs/notify';

    /**
     * Cbs constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 判断该vin码是否支持查询
     *
     * @param vin (车架号(必填))
     * @return JsonResponse ({status : 状态码 ， message : 结果描述 })
     */
    public function getCheckBrand($vin)
    {
        $param = $this->param(null, $vin);
        $url = $this->container->getParameter('yyc_foundation.cbs.url') . "/report/check_brand?" . $param;
        $sendGet = file_get_contents($url);
        return $sendGet;
    }

    /**
     * 购买报告
     * @param vin (车架号(必填))
     * @param $callback
     * @return  JsonResponse 成功：
     * {"status":1,"orderId":"订单号,"message":"描述结果信息"}</br>失败：{"Code":结果代码,"Message":"描述结果信息"}
     *
     */
    public function getBuyReport($vin,$callback)
    {
        $param = $this->param($vin, $callback);
        $url = $this->container->getParameter('yyc_foundation.cbs.url') . "/report/buy_report";
        $sendPost = $this->sentPost($url, $param);
        return $sendPost;
    }


    /**
     * 获取报告状态
     * @param orderid (订单ID(必填))
     * @return JsonResponse {"Code":结果代码,"Message":"描述结果信息"}
     */
    public function getReportStatus($orderid)
    {
        $param = $this->param(null,null,$orderid);
        $url = $this->container->getParameter('yyc_foundation.cbs.url') . "/report/get_report_status?" . $param;
        $sendGet = file_get_contents($url);
        return $sendGet;
    }

    /**
     * 获取报告URL
     * @param orderid (订单ID(必填))
     * @return array key → pcUrl：电脑端url、mobileUrl：手机端url
     */
    public function getReportUrl($orderid)
    {
        $param = $this->param($orderid);
        $urlPC = "http://api.chaboshi.cn/report/show_report?" . $param;
        $urlMobile = "http://api.chaboshi.cn/report/show_reportMobile?" . $param;
        $result = array();
        array_push($result, $urlPC, $urlMobile);
        return $result;
    }

    /**
     * 获取新版报告URL
     * @param orderid (订单ID(必填))
     * @return array key → pcUrl：电脑端url、mobileUrl：手机端url
     */
    public function getNewReportUrl($orderid)
    {
        $param = $this->param(null,null,$orderid);
        $urlPC = $this->container->getParameter('yyc_foundation.cbs.url') . "/new_report/show_report?" . $param;
        $urlMobile = $this->container->getParameter('yyc_foundation.cbs.url') . "/new_report/show_reportMobile?" . $param;
        $result = array();
        array_push($result, $urlPC, $urlMobile);
        return $result;
    }


    /**
     * 获取报告Json数据
     * @param orderid (订单ID(必填))
     * @return array
     */
    public function getReportJson($orderid)
    {
        $param = $this->param(null,null,$orderid);
        $url = $this->container->getParameter('yyc_foundation.cbs.url') . "/report/get_report?" . $param;
        $sendGet = file_get_contents($url);
        return $sendGet;
    }


    /**
     * 获取新版报告Json数据
     * @param orderid (订单ID(必填))
     * @return array
     */
    public function getNewReportJson($orderid)
    {
        $param = $this->param(null,null,$orderid);
        $url = $this->container->getParameter('yyc_foundation.cbs.url') . "/new_report/get_report?" . $param;
        $sendGet = file_get_contents($url);
        return $sendGet;
    }

    /**
     * 拼装参数  ascii码从小到大
     * @param $vin (车架号)
     * @param $orderid (订单id)
     * @param $enginno (发动机号)
     * @param $licensePlate (车牌号)
     * @param $callbackurl (回调地址)
     * @return array
     */
    function param($vin,$callbackurl, $orderid = null, $enginno = null, $licensePlate = null)
    {
        $timestamp = $this->msectime();
        $nonce = uniqid();
        $content = '';
        $content .= 'userid=' . $this->container->getParameter('yyc_foundation.cbs.uid');
        $content .= '&nonce' . '=' . $nonce;
        $content .= '&timestamp' . '=' . $timestamp;
        if ($enginno != null && !empty($enginno)) {
            $content .= 'enginno' . '=' . $enginno;
        }
        if ($licensePlate != null && !empty($licensePlate)) {
            $content .= '&licenseplate' . '=' . $licensePlate;
        }

        if ($orderid != null && !empty($orderid)) {
            $content .= '&orderid' . '=' . $orderid;
        }
        if ($vin != null && !empty($vin)) {
            $content .= '&vin' . '=' . $vin;
        }

        if ($callbackurl != null && !empty($callbackurl)) {
            $content .= '&callbackurl' . '=' . $callbackurl;
        }

        $signAture = null;
        try {
            $signAture = self::sign($content, $this->container->getParameter('yyc_foundation.cbs.key'));
        } catch (\Exception $e) {
            var_dump($e->__toString());
        }
        $content .= '&signature' . '=' . $signAture;
        return $content;
    }


    /*
    *获取毫秒级时间戳
    */
    function msectime()
    {
        list($tmp1, $tmp2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
    }

    /**
     * 通过参数获取签名
     * @param paramsStr (参数串)
     * @param secretkey (商户私钥)
     * @return String (签名串)
     */
    function sign($paramsStr, $secretkey)
    {
        if (empty($paramsStr) || $paramsStr == null || empty($secretkey) || $secretkey == null) {
            return "";
        }
        $i = 0;
        $str = '';
        $paramsStr = explode("&", $paramsStr);
        asort($paramsStr);
        foreach ($paramsStr as $key => $value) {
            if ($i < count($paramsStr) - 1) {
                $str .= $value . '&';
            } else {
                $str .= $value;
            }
            $i++;
        }
        $str = urlencode($str);
        $sign = base64_encode(hash_hmac("sha1", $str, $secretkey, true));
        $sign = urlencode($sign);
        return $sign;
    }

    /*
    *发送post请求
    */
    function sentPost($url, $param)
    {
        $aContext = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $param
            )
        );

        $cxContext = stream_context_create($aContext);
        $d = @file_get_contents($url, false, $cxContext);
        return $d;
    }

}