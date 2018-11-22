<?php

namespace YYC\FoundationBundle\Third;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 老司机接口，返回的都是json格式
 */
class Lsj
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /** 
     * 根据vin码，发动机号()，回调地址(提供个对方用于通知我们的url)，新增车牌号码licence
     * id(给对方的辅助字段)来获取保险相关信息
     */
    public function getInsuranceInfo($vin = null, $engineNumber = null, $licence = null, $notifyUrl = null, $id = '')
    {
        $date = new \DateTime();
        $timeStamp = $date->format('YmdHis').'000';

        $params = array(
            'CallbackUrl' => $notifyUrl,
            'EngineNumber' => $engineNumber,
            'IDNumber' => $id,
            'LicenseNo' => $licence,
            'TimeStamp' => $timeStamp,
            'userid' => $this->container->getParameter('yyc_foundation.lsj.user_id'), 
            'UserToken' => $this->container->getParameter('yyc_foundation.lsj.user_token'),
            'Vin'=> $vin,
        );

        $params['AppSign'] = $this->getSign($params);
        $url = $this->container->getParameter('yyc_foundation.lsj.url');

        //获取保险接口返回的结果
        return $this->httpPost($url, $params);
    }

    /**
     * 根据各参数生成的md5 sign
     */
    public function getSign($params)
    {
        // md5私钥
        $privateKey = $this->container->getParameter('yyc_foundation.lsj.private_key');

        // 拼接成类似get请求的url参数格式
        foreach ($params as $key => $value) {
            $newAry[$key] = $key."=".$value;
        }
        $str = implode('&', $newAry);

        $newStr = $str.$privateKey;
        $sign = md5($newStr);

        return $sign;
    }

    /**
     * 用curl模拟form表单的post提交
     */
    public function httpPost($url, $params)
    {
        $postData = '';
        //create name value pairs seperated by &
        foreach($params as $k => $v)
        {
            $postData .= $k . '='.$v.'&';
        }
        $postData = rtrim($postData, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, count($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        $output=curl_exec($ch);

        curl_close($ch);

        return $output;
    }
}
