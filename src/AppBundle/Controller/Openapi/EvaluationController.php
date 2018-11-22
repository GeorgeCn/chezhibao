<?php

namespace AppBundle\Controller\Openapi;

use AppBundle\Traits\DoctrineAwareTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/openapi/v1/eval")
 */
class EvaluationController extends Controller
{
    use DoctrineAwareTrait;

    /**
     * @Route("/brand", name="eval_brands")
     * @Method("GET")
     */
    public function brandsAction()
    {
        $hessian = $this->get("util.hessian_client_sunwu");

        try {
            $ret = $hessian->getAllCarBrand();
            $ret = json_decode($ret, true);
            foreach ($ret["list"] as $value) {
                if (empty($value[3])) {
                    continue;
                }
                $ret["brands"][] = ["id" => $value[1], "letter" => $value[2], "logo" => $value[3], "name" => $value[0]];
            }
            unset($ret['list']);
            echo json_encode($ret, JSON_UNESCAPED_UNICODE);

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('code' => 1, 'msg' => '异常'));
        }
    }

    /**
     * @Route("/series", name="eval_series")
     * @Method("GET")
     */
    public function seriesAction(Request $request)
    {
        $brandId = $request->query->get('brand_id');
        $hessian = $this->get("util.hessian_client_sunwu");

        try {
            $ret = $hessian->getSeriesByBrand2(intval($brandId));
            $ret = json_decode($ret, true);
            $tmp1 = [];
            foreach ($ret["list"] as $value) {
                $value["name"] = $value["title"];
                unset($value["brandId"]);
                unset($value["title"]);
                unset($value['ratio']);
                $company = $value['company'];
                unset($value['company']);
                $tmp1[$company][] = $value;
            }

            $tmp2 = [];
            $tmp3 = [];
            foreach ($tmp1 as $k => $v) {
                $tmp2['company'] = $k;
                $tmp2['data'] = $v;
                $tmp3[] = $tmp2;
            }

            unset($ret['list']);
            $ret['code']  = 0;
            $ret['msg'] = "";
            $ret['data'] = $tmp3;

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('code' => 1, 'msg' => '异常'));
        }
    }

    /**
     * @Route("/model", name="eval_model")
     */
    public function modelAction(Request $request)
    {
        $seriesId = $request->query->get('series_id');
        $hessian  = $this->get("util.hessian_client_sunwu");

        try {
            $result        = $hessian->getModelBySeries(intval($seriesId));
            $result        = json_decode($result, true);

            $tmp1 = [];
            foreach ($result['list'] as $value) {
                $year = $value['year'];
                unset($value['year']);
                $tmp1[$year][] = $value;
            }

            $tmp2 = [];
            $tmp3 = [];

            foreach ($tmp1 as $k => $v) {
                $tmp2['year'] = $k;
                $tmp2['data'] = $v;
                $tmp3[] = $tmp2;
            }

            $ret['data'] = $tmp3;
            $ret['code']  = 0;
            $ret['msg'] = "";

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('code' => 1, 'msg' => '异常'));
        }
    }

    /**
     * @Route("/search", name="eval_search")
     */
    public function searchAction(Request $request)
    {
        $search  = $request->query->get('search');
        $hessian = $this->get("util.hessian_client_sunwu");

        try {
            $result        = $hessian->search($search);
            $result        = json_decode($result, true);
            $ret['data']   = $result["list"];
            $ret['code']  = 0;
            $ret['msg'] = "";

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('code' => 1, 'msg' => '异常'));
        }
    }

    /**
     * @Route("/do", name="eval_do")
     */
    public function doAction(Request $request)
    {
        $hessian = $this->get("util.hessian_client_polaris");
    
        $modelId = intval($request->query->get('modelId', null));
        // $modelId = 8992;
        $registerDate = $request->query->get('registerDate', null);
        // $registerDate = '2008-03-11';
        $kilometer = floatval($request->query->get('kilometer', null));
        // $kilometer = 12.4521;
        $city = $request->query->get('city', null);
        // $city = '上海市';
        $grade = '2';
        $futureYear = '0,0.25,0.5,0.75,1';
        $from = 'app';

        if(empty($modelId)||empty($registerDate)||empty($kilometer)||empty($city)) {
            return new JsonResponse(array('code' => 1, 'msg' => '参数异常'));
        }
        try {
            $res = $hessian->valuationFuturePriceByYear($modelId, $registerDate, $kilometer, $grade, $city, $futureYear, 1, $from);
        
            if(empty($res['error'])) {
                $mock = [];
                $string = [0, 3, 6, 9, 12];

                foreach($res as $k => &$v) {
                    for($i=0; $i<5; $i++) {
                        if($i > 0) {
                            $j = $i-1;
                            if( $v[$i]['price'] < $v[$j]['price']) {
                                $v[$j]['price'] = $v[$i]['price'] * 0.98;
                            }
                        }
                            $mock['price'][$k][$i]['date_value'] = $string[$i];
                            $mock['price'][$k][$i]['price'] = $res[$k][$i]['price'];            
                    }
                    
                }
                // $deal = '[{"name":"2015款 君威荣耀","price":10.2700,"city":"\u6df1\u5733\u5e02","type":"\u4e8c\u7ea7","mileage":5.3951,"reg":"2013-06","dealAt":"2017.05.15"},{"name":"2015款 君威荣耀","price":12.8000,"city":"\u9752\u5c9b\u5e02","type":"\u4e8c\u7ea7","mileage":16.933,"reg":"2015-01","dealAt":"2017.03.15"},{"name":"2015款 君威荣耀","price":10.8100,"city":"\u676d\u5dde\u5e02","type":"\u4e8c\u7ea7","mileage":3.7661,"reg":"2013-03","dealAt":"2017.03.08"},{"name":"2015款 君威荣耀","price":10.6800,"city":"\u897f\u5b89\u5e02","type":"\u4e8c\u7ea7","mileage":5.9239,"reg":"2013-06","dealAt":"2016.12.18"},{"name":"2015款 君威荣耀","price":9.5700,"city":"\u82cf\u5dde\u5e02","rating":"\u4e09\u7ea7","mileage":4.0793,"reg":"2012-10","dealAt":"2016.12.15"}]'; 
                // $mock['deals'] = json_decode($deal,true);
                $mock['deals'] = [];
            } else {
                return new JsonResponse(array('code' => 1, 'msg' => '暂无数据，请稍后再试'));
            }

        
            $ret['data'] = $mock;
            $ret['code']  = 0;
            $ret['msg'] = "";

            return new JsonResponse($ret);
        } catch (\Exception $e) {
            return new JsonResponse(array('code' => 1, 'msg' => '异常'));
        }
    }
}