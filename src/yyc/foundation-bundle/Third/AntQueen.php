<?php

namespace YYC\FoundationBundle\Third;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 蚂蚁女王
 * http://backend.mayinvwang.com/api2.php
 */
class AntQueen
{
    const GETTOKEN_URL = "http://openapi.mayinvwang.com/OpenPublicApi/getToken";
    const CHECKVIN_URL = "http://openapi.mayinvwang.com/OpenApi/checkVin";
    const QUERYBYVIN_URL = "http://openapi.mayinvwang.com/OpenApi/queryByVin";

    private $lastError = "";

    public function getLastError()
    {
        return $this->lastError;
    }

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function getPartnerId()
    {
        return $this->container->getParameter('yyc_foundation.ant_queen.partner_id');
    }

    private function getKey()
    {
        return $this->container->getParameter('yyc_foundation.ant_queen.key');
    }

    public function getToken()
    {
        $params["partner_id"] = $this->getPartnerId();
        $params["secret_key"] = $this->getKey();
        $ret = $this->mockStub(self::GETTOKEN_URL, $params);
        if ($ret === false || !isset($ret["token"])) {
            $this->lastError = "token获取失败！";
            return false;
        }
        return $ret["token"];
    }

    public function queryByVin($vin, $engine, $order_id)
    {
        $token = $this->getToken();
        if ($token === false) {
            return false;
        }
        $is_text = 1;
        $params["vin"] = $vin;
        $params["is_text"] = $is_text;
        $params["token"] = $token;
        $params["secret_key"] = $this->getKey();
        $ret = $this->mockStub(self::CHECKVIN_URL, $params);
        if ($ret === false || ($ret["code"] != 200 && $ret["code"] != 402)) {
            if (isset($ret["message"])) {
                $this->lastError = $ret["message"];
            }
            else{
                $this->lastError("check 出错！");
            }
            
            return false;
        }
        if ($ret["code"] == 402) {
            $is_text = 0;
        }
        $params["img_url"] = $vin;
        $params["order_id"] = $order_id;
        $params["is_text"] = $is_text;
        $params["engine_num"] = $engine;
        $params["token"] = $this->getToken();
        $ret = $this->mockStub(self::QUERYBYVIN_URL, $params);
        if ($ret === false || $ret["code"] != 200) {
            $this->lastError = "提交失败！";
            return false;
        }
        return $ret["query_id"];
    }

    public function getMockData()
    {
        return [
            'notify_time' => '2017-08-10 10:40:21',
            'notify_type' => 'mynw',
            'notify_id' => 'mynw',
            'vin' => 'LSGPC52U0BF099300',
            'order_id' => 'B81032807826626560',
            'query_id' => '1111111111',
            'result_images' => 'http://7xki7s.com1.z0.glb.clouddn.com/hpl/Fuhlv97rgWmPtsqSH13vTQziCleW?imageView2/1/w/228/h/158",http://7xki7s.com1.z0.glb.clouddn.com/hpl/FjIuQTLlMG3s2BKcjOh54XZ3xQfg?imageView2/1/w/228/h/158,http://7xki7s.com1.z0.glb.clouddn.com/hpl/FqAVsvGfzLR2I6i8C90vKq6MCOH8?imageView2/1/w/228/h/158',
            'number_of_accidents' => 0,
            'last_time_to_shop' => '2016-12-19',
            'text_contents_json' => 'null',
            'result_description' => '',
            'total_mileage' => 48512,
            'car_brand_id' => 98,
            'car_brand' => '雪佛兰',
            'car_info' => '["结构部件正常","发动机正常","里程数正常","安全气囊正常"]',
            'car_status' => '[{"title":"结构部件","desc":"正常","status":1},{"title":"发动机","desc":"正常","status":1},{"title":"里程数","desc":"正常","status":1},{"title":"安全气囊","desc":"正常","status":1}]',
            'gmt_create' => '2017-08-10 10:40:08',
            'gmt_finish' => '2017-08-10 10:40:21',
            'order_char' => '',
            'query_text' => '[{"date":"2016-12-19","detail":"机油散热器密封圈更换;","kilm":"48512","remark":"索赔","cailiao":"发动机机油冷却器密封件;机油滤清器适配器密封件(O形圈);发动机机油冷却器密封件;","other":"雪佛兰D11 1.6L\\/1.8L机油散热器密封圈更换;","img_url":["http://7xki7s.com1.z0.glb.clouddn.com/hpl/Fuhlv97rgWmPtsqSH13vTQziCleW?imageView2/1/w/228/h/158","http://7xki7s.com1.z0.glb.clouddn.com/hpl/FoGkAM8Cn69_0VywYCVSZKG_6mTN?imageView2/1/w/228/h/158"]},{"date":"2015-10-01","detail":"九项免费检测;更换点火线圈;更换火花塞;","kilm":"35712","remark":"小修","cailiao":"点火线圈;火花塞总成;","other":"","img_url":["http://7xki7s.com1.z0.glb.clouddn.com/hpl/FoGkAM8Cn69_0VywYCVSZKG_6mTN?imageView2/1/w/228/h/158","http://7xki7s.com1.z0.glb.clouddn.com/hpl/FoGkAM8Cn69_0VywYCVSZKG_6mTN?imageView2/1/w/228/h/158"]}]',
            'returnCarInfo' => 'false',
            'result_status' => 'QUERY_SUCCESS'
        ];
    }

    private function mockStub($url, $params)
    {
        if ($params["secret_key"] != "debug") {
            $curl = $this->container->get('util.curl_helper');
            $ret = $curl->post($url, $params);
            if ($ret === false) {
                return false;
            }
            return json_decode($ret, true);
        }
        switch ($url) {
            case self::GETTOKEN_URL:
                $json = '{"code":"200","token":"a21e2b37a116a6307c3b83fff622a5bb"}';
                break;
            case self::CHECKVIN_URL:
                $json = '{"code":"200"}';
                break;
            case self::QUERYBYVIN_URL:
                $json = '{"code":"200","query_id":"1111111111"}';
                break;
        }

        return json_decode($json, true);
    }
}
