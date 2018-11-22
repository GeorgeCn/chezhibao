<?php

namespace YYC\FoundationBundle\Third;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 车鉴定接口
 */
class Cjd
{
    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
    }

    /**
     * 获取账户余额
     */
    public function getBalance()
    {
        $params = array(
            'uid' => $this->container->getParameter('yyc_foundation.cjd.uid'),
            'time' => date('Y-m-d H:i:s'),
        );

        $params['sign'] = urlencode($this->getSign($params));
        $url = $this->container->getParameter('yyc_foundation.cjd.url').'/rest/publicif/accountInfo';

        return $this->httpPost($url, $params);
    }

    /**
     * 根据vin码，发动机号(可选)，orderNo(将我们的唯一标示id传给车鉴定，对账时能用到)
     * 来购买报告
     * 
     */
    public function buy($vin = null, $engineNumber = null, $orderNo = null)
    {
        $params = array(
            'uid' => $this->container->getParameter('yyc_foundation.cjd.uid'),
            'vin' => $vin,
            'time' => date('Y-m-d H:i:s'),
        );

        $params['sign'] = urlencode($this->getSign($params));
        // 可选的参数不参与签名，空值也可以post传递过去
        $params['engine'] = $engineNumber;
        $params['orderNo'] = $orderNo;

        $url = $this->container->getParameter('yyc_foundation.cjd.url').'/publicif/2.0/buy';

        //获取购买返回的结果 
        return $this->httpPost($url, $params);
    }

    /**
     * 当车鉴定结果出来时会主动告诉我们结果出来了，但具体的json内容他们不会一并推送过来，需要我们自己再去单独获取
     * 根据购买报告时车鉴定返回的orderId去获取报告的内容
     * 
     */
    public function getMaintainInfo($orderId = null)
    {
        $params = array(
            'uid' => $this->container->getParameter('yyc_foundation.cjd.uid'),
            'oid' => $orderId,
            'time' => date('Y-m-d H:i:s'),
        );

        $params['sign'] = urlencode($this->getSign($params));

        $url = $this->container->getParameter('yyc_foundation.cjd.url').'/rest/publicif/2.0/reportData';

        //获取返回具体报告的内容 
        return $this->httpPost($url, $params);
    }

    /**
     * $params待签名参数
     * 用的是RSA签名
     * 最后的签名，需要用base64编码
     * return Sign签名
     */
    public function getSign($params)
    {
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $priKeyFile = $rootDir.'/'.$this->container->getParameter("yyc_foundation.cjd.rsa_private_key");

        //读取私钥文件
        $priKey = file_get_contents($priKeyFile);
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥,返回的是resource
        $res = openssl_get_privatekey($priKey);

        //将传过来的参数数组转换为字符串，同时加上密码字符串
        $pwd = $this->container->getParameter("yyc_foundation.cjd.pwd");
        $data = implode('', $params).$pwd;

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res);
        //释放资源
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);

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
