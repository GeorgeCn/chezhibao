<?php

namespace AppBundle\Third;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Sms
{
    private $appKey;
    private $appSecret;
    private $url;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->appKey = $this->container->getParameter('sms_appKey');
        $this->appSecret = $this->container->getParameter('sms_appSecret');
        $this->url = $this->container->getParameter('sms_url');
    }

    /**
    * template 传模板code,  yjc_examer_pass, yjc_examer_refuse, yjc_examer_back, yjc_sms_code
    * deviceType   WebApp(1), ANDROID(2), IOS(3), RTX(4), SMS(5),Mail(6),Dingtalk(8),YouMeng(9);
    * sendType 1为及时发送，0为延迟发送
    * 按key名从小到大排
    */
    public function send($template = '', $phoneNum = '', $contentParam = [], $msgTitle = 'yunjiance', $sendType = 1, $deviceType = 5, $msgResource = 1)
    {
        if ($this->container->getParameter('sms_switch') === false) {
            return ;
        }
        //对模板变量参数array转成json
        $tmp = json_encode($contentParam, JSON_FORCE_OBJECT);
        $params = [
            'appKey' => $this->appKey,
            'contentParam' => $tmp,
            'deviceType' => $deviceType,
            'msgResource' => $msgResource,
            'msgTitle' => $msgTitle,
            'phoneNum' => $phoneNum,
            'sendType' => $sendType,
            'template' => $template,
        ];

        $sign = $this->sign($params);
        $params['sign'] = $sign;

        return $this->container->get('util.curl_helper')->post($this->url, $params);
    }

    public function sign($params)
    {
        $data = '';

        foreach ($params as $key => $value) {
            $data .= $key.'='.$value;
        }

        $data = $data.$this->appSecret;

        return md5(urlencode($data));
    }
}



