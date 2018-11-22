<?php

namespace AppBundle\Third;

use AppBundle\Traits\ContainerAwareTrait;

/**
 * 第一车网接口，返回的都是json格式
 */
class Dyc
{
    use ContainerAwareTrait;

    //下面的2个常量由第一车网提供给我们 
    const API_KEY = 'cf27a576-d28e-4d01-b88d-4ea920ca2ebc';
    const VALIDATE_KEY = '6dd3f3d4-8c4d-41d1-9087-9c81abd3f204';

    /**
     * 获取车品牌列表
     */
    public function getBrands()
    {
        $apiKey = self::API_KEY;
        $date = date('Y-m-d');
        $hour = date('H');
        $minute = date('i');
        $second = date('s');

        $secret = $this->getSign();
        $url = "http://car.iautos.cn/Maverick/CarBrand/ApiKey/$apiKey/$date/$hour/$minute/$second/$secret";

        return $this->httpGet($url);
    }

    /**
     * 根据品牌获取车系
     */
    public function getSeries($brandId = null)
    {
        $apiKey = self::API_KEY;
        $date = date('Y-m-d');
        $hour = date('H');
        $minute = date('i');
        $second = date('s');

        $params = $brandId;
        $secret = $this->getSign($params);
        $url = "http://car.iautos.cn/Maverick/CarMfrs/ApiKey/$apiKey/$brandId/$date/$hour/$minute/$second/$secret";

        return $this->httpGet($url);
    }

    /**
     * 根据车系获取购买年份
     */
    public function getPurchaseYears($seriesId = null)
    {
        $apiKey = self::API_KEY;
        $date = date('Y-m-d');
        $hour = date('H');
        $minute = date('i');
        $second = date('s');

        $params = $seriesId;
        $secret = $this->getSign($params);
        $url = "http://car.iautos.cn/Maverick/PurchaseYear/ApiKey/$apiKey/$seriesId/$date/$hour/$minute/$second/$secret";

        return $this->httpGet($url);
    }

    /**
     * 根据车系和购买年份获取车型
     */
    public function getModels($seriesId = null, $purchaseYear = null)
    {
        $apiKey = self::API_KEY;
        $date = date('Y-m-d');
        $hour = date('H');
        $minute = date('i');
        $second = date('s');

        $params = $seriesId.$purchaseYear;
        $secret = $this->getSign($params);
        $url = "http://car.iautos.cn/Maverick/Model/ApiKey/$apiKey/MatchModel/$seriesId/$purchaseYear/$date/$hour/$minute/$second/$secret";

        return $this->httpGet($url);
    }

    /**
     * 根据车型和购买年份获取价格
     */
    public function getPrices($modelId = null, $purchaseYear = null)
    {
        $apiKey = self::API_KEY;
        $date = date('Y-m-d');
        $hour = date('H');
        $minute = date('i');
        $second = date('s');

        $params = $modelId.$purchaseYear;
        $secret = $this->getSign($params);
        $url = "http://car.iautos.cn/Maverick/Price/ApiKey/$apiKey/$modelId/$purchaseYear/$date/$hour/$minute/$second/$secret";

        return $this->httpGet($url);
    }

    /**
     * 获取根据各参数生成的md5 sign
     */
    public function getSign($params = null)
    {
        $validateKey = self::VALIDATE_KEY;
        $dateTime = date('Y-m-d H:i:s');

        if ($params) {
            $sign = md5($params.$dateTime.$validateKey);
        } else {
            $sign = md5($dateTime.$validateKey);
        }

        return $sign;
    }

    /**
     * curl模拟get请求
     */
    public function httpGet($url, $params = null)
    {
        //初始化
        $ch = curl_init();

        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //执行并获取请求内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);

        return $output;
    }
}
