<?php

namespace AppBundle\Controller\PriceModel;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Traits\DoctrineAwareTrait;
use AppBundle\Entity\Report;

/**
 * @Route("/price")
 */
class DefaultController extends Controller
{
    use DoctrineAwareTrait;

    /**
     * @Route("/", name="get_price")
     * @Method("GET")
     */
    public function priceAction(Request $request)
    {
        $hessian = $this->get("util.hessian_client_polaris");
        $reportID = $request->query->get('reportID');
    
        $em = $this->getDoctrine()->getManager();
        $report = $em->getRepository("AppBundle:Report")->findOneBy(array("id"=>$reportID));
        $modelId = intval($request->query->get('modelId'));
        $registerDate = strtr($request->query->get('registerDate'),"/","-");
        $kilometer = floatval($request->query->get('kilometer')/10000);
        $grade = '2';
        $city = $request->query->get('city');
        $futureYear = '0,1,2';
        $from = 'hpl';
        $data = [];

        if(empty($modelId)||empty($registerDate)||empty($kilometer)||empty($city)) {
            return new JsonResponse(["success"=>false, "data"=>$data]);
        }

        try {
            $ret = $hessian->valuationFuturePriceByYear($modelId, $registerDate, $kilometer, $grade, $city, $futureYear, 1, $from);
                      
            if(empty($ret['error'])) {
                $data['current']['price'] = $ret['normal'][0]['price'];
                $data['current']['accuracy'] = $this->levelPriceSwitch($ret['normal'][0]['level']);
                $data['current']['model'] = $this->levelPriceSwitch(null, $ret['normal'][0]['flag']);
                $data['current']['max'] = null;
                $data['current']['min'] = null;
                $data['one_year']['price'] = $ret['normal'][4]['price'];
                $data['one_year']['accuracy'] = $this->levelPriceSwitch($ret['normal'][4]['level']);
                $data['one_year']['model'] = $this->levelPriceSwitch(null, $ret['normal'][4]['flag']); 
                $report->setBjxResult($data);
                $em->flush();
                // $deal = '[{"carAge":99,"price":10.2700,"city":"\u6df1\u5733\u5e02","type":"\u4e8c\u7ea7","mileage":5.3951,"reg":"2013-06","dealAt":"2017.05.15"},{"carAge":99,"price":12.8000,"city":"\u9752\u5c9b\u5e02","type":"\u4e8c\u7ea7","mileage":16.933,"reg":"2015-01","dealAt":"2017.03.15"},{"carAge":99,"price":10.8100,"city":"\u676d\u5dde\u5e02","type":"\u4e8c\u7ea7","mileage":3.7661,"reg":"2013-03","dealAt":"2017.03.08"},{"carAge":99,"price":10.6800,"city":"\u897f\u5b89\u5e02","type":"\u4e8c\u7ea7","mileage":5.9239,"reg":"2013-06","dealAt":"2016.12.18"},{"carAge":99,"price":9.5700,"city":"\u82cf\u5dde\u5e02","rating":"\u4e09\u7ea7","mileage":4.0793,"reg":"2012-10","dealAt":"2016.12.15"}]';
                // $data['deals'] = json_decode($deal,true);
                $data['deals'] = [];
                return new JsonResponse(["success"=>true, "data"=>$data]);
            } else {
                return new JsonResponse(["success"=>false, "data"=>$data, "errmsg"=>$ret['error']]);
            }        
        } catch (\Exception $e) {
            return new JsonResponse(array('errno' => 1, 'errmsg' => '异常'));
        }
    }

    public function levelPriceSwitch ($level = null, $flag = null) 
    {
        if($level) {
            switch($level) {
                case 'A' : $level = 4;break;
                case 'B' : $level = 3;break;
                case 'C' : $level = 2;break;
                case 'D' : $level = 1;break;
                default  : $level = 0;break;
                } 

            return $level;
        }
        if($flag) {
            switch($flag) {
                case 1   : $flag = '车型训练模型1';break;
                case 2   : $flag = '车型训练模型2';break;
                case 3   : $flag = '车型训练模型3';break;
                case 8   : $flag = '默认1-20年保值率';break;
                case 9   : $flag = '车型样本数入库';break;
                case 10  : $flag = '决策树模型';break;
                default  : $flag = '无车型训练模型';break;
                } 

            return $flag;
        }

        return null;
    }
}
