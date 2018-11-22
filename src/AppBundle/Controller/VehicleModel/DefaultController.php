<?php

namespace AppBundle\Controller\VehicleModel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Traits\DoctrineAwareTrait;

/**
 * @Route("/vm")
 */
class DefaultController extends Controller
{
    use DoctrineAwareTrait;

    /**
     * @Route("/allbrands", name="vm_brands")
     * @Method("GET")
     */
    public function brandsAction()
    {
        $hessian = $this->get("util.hessian_client_sunwu");

        try {
            $ret = $hessian->getAllCarBrand();
            $ret = json_decode($ret, true);
            $ret['errno'] = 0;
            $ret['errmsg'] = "";
            $ret['data']['list'] = $ret['list'];
            unset($ret['list']);

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('errno' => 1, 'errmsg' => '异常'));
        }
    }

    /**
     * @Route("/series", name="vm_series")
     * @Method("GET")
     */
    public function seriesAction(Request $request)
    {   
        $brandId = $request->query->get('brandid');
        $hessian = $this->get("util.hessian_client_sunwu");

        try {
            $ret = $hessian->getSeriesByBrand(intval($brandId));
            $ret = json_decode($ret, true);
            $ret['errno'] = 0;
            $ret['errmsg'] = "";
            $ret['data']['list'] = $ret['list'];
            unset($ret['list']);

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('errno' => 1, 'errmsg' => '异常'));
        }
    }

    /**
     * @Route("/niankuan", name="vm_niankuan")
     * @Method("GET")
     */
    public function niankuanAction(Request $request)
    {
        $seriesId = $request->query->get('seriesid');
        $proYear = $request->query->get('proyear', "");
        $hessian = $this->get("util.hessian_client_sunwu");

        try {
            $result = $hessian->getModelBySeriesAndYear(intval($seriesId), $proYear);
            $result = json_decode($result, true);
            $ret['data']['list']  = $result;
            $ret['errno'] = 0;
            $ret['errmsg'] = "";

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('errno' => 1, 'errmsg' => '异常'));
        }
    }

    /**
     * @Route("/vin_series", name="vm_vin_series")
     * @Method("GET")
     */
    public function vinSeriesAction(Request $request)
    {
        $vin = $request->query->get('vin');
        $ret = [
            "errno" => 0,
            "errmsg" => "",
            "data" => [
                "seriesid" => "",
                "brand" => "",
                "series" => ""
            ]
        ];
        $levelIDs = [];
        try {
            $levelIDs = $this->get('Liyang')->matchVin($vin);
            //$levelIDs = ['CFT0540A0111', 'CFT0540A0112', 'CFT0540A0113', 'CFT0540A0114', 'CFT0540A0115',];
        } catch (\Exception $e) {
            $ret["errno"] = 30;
            $ret["errno"] = "找不到seriesid";
            return JsonResponse::create($ret);
        }

        $liyangs = $this->getRepo("AppBundle:Liyang")->findBy(["id" => $levelIDs]);
        if (count($liyangs) == 0) {
            $ret["errno"] = 30;
            $ret["errno"] = "找不到seriesid";
            return JsonResponse::create($ret);
        }

        $hessian = $this->get("util.hessian_client_sunwu");
        $brand = $liyangs[0]->getBrand();
        $series = $liyangs[0]->getSeries();
        $productCt = $liyangs[0]->getProdcutCt();
        if ($productCt != "进口") {
            $productCt = "国产";
        }
        // 有些车系，有中英文的问题，中文的车系会在[]里，需要提取出来，再查询次。
        preg_match("/^(?<s1>.*) \[(?<s2>.+)\]$/", $series, $outputs);
        $serieses = [];
        if (isset($outputs["s2"])) {
            $serieses[] = $outputs["s1"];
            $serieses[] = $outputs["s2"];
        }
        else{
            $serieses[] = $series;
        }

        foreach ($serieses as $value) {
            $tmp = $hessian->getSeriesId($brand, $value, $productCt);
            $tmp = json_decode($tmp, true);
            if (!empty($tmp)) {
                $ret["data"]["seriesid"] = $tmp[0]["seriesId"];
                $ret["data"]["brand"] = $tmp[0]["brand"];
                $ret["data"]["series"] = $tmp[0]["series"];
                return JsonResponse::create($ret);
            }
        }


        $ret["errno"] = 30;
        $ret["errno"] = "找不到seriesid";
        return JsonResponse::create($ret);
    }

    /**
     * @Route("/model_detail", name="vm_model_detail")
     * @Method("GET")
     */
    public function modelDetailAction(Request $request)
    {
        $modelId = $request->query->get('modelid');
        $hessian = $this->get("util.hessian_client_sunwu");

        try {
            $result = $hessian->getConfByModelId(intval($modelId));
            $result = json_decode($result, true);
            $ret['data']  = $result;
            $ret['errno'] = 0;
            $ret['errmsg'] = "";
            $ret['data']['modelId'] = $modelId; 

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('errno' => 1, 'errmsg' => '异常'));
        }
    }

    /**
     * @Route("/model", name="vm_model")
     */
    public function modelAction(Request $request)
    {
        $seriesId = $request->query->get('seriesid');
        $niankuan = $request->query->get('niankuan');
        $hessian = $this->get("util.hessian_client_sunwu");
        $parameters = $request->request->all();

        $str = '';
        foreach ($parameters as $k => $v) {
            $str .= $k.':'.$v.',';
        }
        $str = rtrim($str, ',');

        try {
            $result = $hessian->getSeriesConfBySeriesIdAndYear(intval($seriesId), $niankuan, $str);
            $result = json_decode($result, true);
            // 取4个汽车之家的id给到compare
            $modelids = [];
            foreach ($result["list"] as &$value) {
                if (count($modelids) < 4) {
                    $modelids[] = $value[1];
                }
                $value[1] = "http://car.autohome.com.cn/config/spec/{$value[1]}.html";
            }
            $result['compare'] = "http://car.autohome.com.cn/duibi/chexing/carids=".implode(',', $modelids);
            $ret['data']  = $result;
            $ret['errno'] = 0;
            $ret['errmsg'] = "";

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('errno' => 1, 'errmsg' => '异常'));
        }
    }

    /**
     * @Route("/auction_info", name="vm_auction_info")
     */
    public function auctionInfo(Request $request)
    {
        $modelId = $request->query->get('modelId');
        $year = $request->query->get('year');

        $tmp = $this->get("util.hessian_client_sunwu")->GetCarAuctionInfo(intval($modelId), intval($year));
        if ($tmp === false) {
            return JsonResponse::create(['errno' => 1, 'errmsg' => '异常']);
        }
        $tmp = json_decode($tmp, true);
        $ret['data'] = $tmp;
        $ret['errno'] = 0;
        $ret['errmsg'] = "";
        return JsonResponse::create($ret);
    }
}
