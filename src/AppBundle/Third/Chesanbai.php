<?php

namespace AppBundle\Third;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Chesanbai
{
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /*
     * 这些是从youyiche项目copy过来的，但getUsedCarPrice有扩展过，以后请维护这里~
     */
    const Token = "187393449a90324c1e64178ec2ca5dde";
    const Url = "http://api.che300.com/service/PublicService.php";
    static $strLastError = "";

    public static function getLastError(){
        return self::$strLastError;
    }

    public static function getEvalPriceByVIN ($vin, $regDate, $mile){
        $params["oper"] = "getEvalPriceByVIN";
        $params["token"] = self::Token;
        $params["VIN"] = $vin;
        $params["regDate"] = $regDate;
        $params["mileAge"] = $mile;
        $params["cityId"] = 3;
        $url = "http://api.che300.com/service/PublicService.php";
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($c, CURLOPT_POSTFIELDS, $params);
        $ret = curl_exec($c);
        $retArr = json_decode($ret, true);
        curl_close($c);
        if ($retArr["status"] == 0) {
            echo "$ret\n";
            return null;
        }

        $price = [];
        foreach (current($retArr["result"]) as $results) {
            foreach ($results["eval_result"] as $result) {
                $eval_price = json_decode(current($result)["eval_price"], true);
                $price[] = $eval_price['dealer_buy_price'];
            }
        }
        sort($price);
        if (count($price) == 0) {
            return null;
        }
        return $price[0];
    }

    public static function getUsedCarPrice ($modelId, $regDate, $mile, $cityId){
        $params["oper"] = "getUsedCarPrice";
        $params["token"] = self::Token;
        $params["modelId"] = $modelId;
        $params["regDate"] = $regDate;
        $params["mile"] = $mile;
        $params["zone"] = $cityId;
        $url = "http://api.che300.com/service/PublicService.php";
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($c, CURLOPT_POSTFIELDS, $params);
        $ret = curl_exec($c);
        $retArr = json_decode($ret, true);
        curl_close($c);
        if ($retArr["status"] == 0) {
//            echo "$ret\n";
            self::$strLastError = $retArr["error_msg"];
        }
        return $retArr;
    }
    
    /*
     * 这些是我自己写的
     */
    /**
     * "专业"估价接口，里面返回了现在的估价、以及明后两年的预估估价
     * @param $modelId
     * @param $regDate
     * @param $makeDate string 其实不是必须的，但车三百对regDate有“合理范围”限制，如果超过范围，将得不到结果，该字段就是为了解决这个问题的
     * @param $mile
     * @param $color
     * @param $cityId
     * @param $state array 外观、内饰、工况，顺序不能变
     * @return mixed
     */
    public function getUsedCarPriceAnalysis($modelId, $regDate, $makeDate, $mile, $color, $cityId, $state)
    {
        $params["token"] = self::Token;
        $params["modelId"] = $modelId;
        $params["regDate"] = $regDate;
        $params["makeDate"] = $makeDate;
        $params["mile"] = $mile;
        $params["color"] = $color;
        $params["zone"] = $cityId;
        list($params['surface'], $params['interior'], $params['work_state']) = $state;
        $url = "http://api.che300.com/service/***/getUsedCarPriceAnalysis?".http_build_query($params);
        $ret = file_get_contents($url);
        $retArr = json_decode($ret, true);
        if ($retArr["status"] == 0) {
//            echo "$ret\n";
            self::$strLastError = $retArr["error_msg"];
        }
        return $retArr;
    }

    /**
     * 返回某个model的车子信息（可以用来【获取新车指导价】）
     * @param $modelId
     * @return mixed
     */
    public function getCarModelInfo($modelId)
    {
        $params["token"] = self::Token;
        $params["modelId"] = $modelId;
        $url = "http://api.che300.com/service/getCarModelInfo?".http_build_query($params);
        $ret = file_get_contents($url);
        $retArr = json_decode($ret, true);
        if ($retArr["status"] == 0) {
//            echo "$ret\n";
            self::$strLastError = $retArr["error_msg"];
        }
        return $retArr;
    }
    
    /**
     * 返回所有的品牌列表。
     * @link http://www.che300.com/open?current=5
     * @return mixed|null
     */
    public function getCarBrandList()
    {
        $params['token'] = self::Token;
        $url = 'http://api.che300.com/service/getCarBrandList';
        $ret = file_get_contents($url.'?token='.$params['token']);
        $retArr = json_decode($ret, true);

        if ($retArr["status"] == 1) {
            return $retArr['brand_list'];
        }
        echo "$ret\n";
        self::$strLastError = $retArr["error_msg"];
        return null;
    }

    /**
     * 返回指定品牌下面的所有车系列表。
     * @link http://www.che300.com/open?current=4
     * @param $params
     * @return null
     * @throws \Exception
     */
    public function getCarSeriesList($params)
    {
        $params['token'] = self::Token;
        $brandList = $this->getCarBrandList();
        foreach ($brandList as $brand) {
            if ($brand['brand_name'] == $params['brand']) {
                $params['brandId'] = $brand['brand_id'];
                break;
            }
        }
        if (empty($params['brandId'])) {
            throw new \Exception('天呐车300品牌数据库里没有找到该品牌！');
        }
        $url = 'http://api.che300.com/service/getCarSeriesList';
        $ret = file_get_contents("{$url}?token={$params['token']}&brandId={$params['brandId']}");
        $retArr = json_decode($ret, true);

        if ($retArr["status"] == 1) {
            return $retArr['series_list'];
        }
        echo "$ret\n";
        self::$strLastError = $retArr["error_msg"];
        return null;
    }

    /**
     * 返回指定车系下面的所有车型。
     * @link http://www.che300.com/open?current=3
     * @param $params
     * @return null
     * @throws \Exception
     */
    public function getCarModelList($params)
    {
        $params['token'] = self::Token;
        $url = 'http://api.che300.com/service/getCarModelList';
        $ret = file_get_contents("{$url}?token={$params['token']}&seriesId={$params['seriesId']}");
        $retArr = json_decode($ret, true);

        if ($retArr["status"] == 1) {
            return $retArr['model_list'];
        }
        echo "$ret\n";
        self::$strLastError = $retArr["error_msg"];
        return null;
    }

    /**
     * 返回所有的城市列表。
     * @link http://www.che300.com/open?current=6
     * @return mixed|null
     */
    public function getAllCity()
    {
        $params['token'] = self::Token;
        $url = 'http://api.che300.com/service/getAllCity';
        $ret = file_get_contents($url.'?token='.$params['token']);
        $retArr = json_decode($ret, true);

        if ($retArr["status"] == 1) {
            return $retArr['city_list'];
        }
        echo "$ret\n";
        self::$strLastError = $retArr["error_msg"];
        return $retArr;
    }
}
