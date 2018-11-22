<?php

namespace YYC\FoundationBundle\Third;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 大圣来了接口，返回的都是json格式
 */
class Dsll
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 获取余额
     */
    public function getBalance()
    {
        $params = array(
            'interface' => 'query_account_balance',
            'partner' => $this->container->getParameter('yyc_foundation.dsll.partner'),
            'sign_type' => 'MD5',
            'sign' => '',
        );

        $params['sign'] = $this->getSign($params);
        $url = $this->container->getParameter('yyc_foundation.dsll.url');

        return $this->httpPost($url, $params);
    }

    /**
     * 获取品牌列表
     */
    public function getBrands()
    {
        $params = array(
            'interface' => 'get_available_car_brands_list',
            'partner' => $this->container->getParameter('yyc_foundation.dsll.partner'),
            'sign_type' => 'MD5',
            'sign' => '',
        );

        $params['sign'] = $this->getSign($params);
        $url = $this->container->getParameter('yyc_foundation.dsll.url');

        return $this->httpPost($url, $params);

    }

    /**
     * 根据vin码，品牌，发动机号()，id(大圣来了异步通知我们时，
     * 可以根据这个id做相应的逻辑)，回调地址(提供个大圣来了用于通知我们的url)
     * 来获取维修信息
     */
    public function getMaintainInfo($vin = null, $brandId = null, $engineNumber = null, $id = null, $notifyUrl = null)
    {
        $params = array(
            'interface' => 'create_query_policy_by_partner',
            'partner' => $this->container->getParameter('yyc_foundation.dsll.partner'),
            'sign_type' => 'MD5',
            'sign' => '',
            '_input_charset' => 'UTF-8',
            'vin' => $vin,
            'notify_url' => $notifyUrl,
            'order_id' => $id,
        );

        $params['sign'] = $this->getSign($params);
        $url = $this->container->getParameter('yyc_foundation.dsll.url');

        //获取维修记录返回的结果
        return $this->httpPost($url, $params);
    }

    /**
     * 根据各参数生成的md5 sign
     */
    public function getSign($params)
    {
        // md5私钥
        $privateKey = $this->container->getParameter('yyc_foundation.dsll.private_key');

        unset($params['sign_type']);
        unset($params['sign']);
        // 按键名升序排列
        ksort($params);
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
