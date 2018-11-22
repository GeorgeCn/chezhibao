<?php

namespace AppBundle\Business;

use AppBundle\Traits\ContainerAwareTrait;
use AppBundle\Entity\OrderBack;
use AppBundle\Entity\Report;
use AppBundle\Model\MetadataManager;

class ShowPdfHtmlLogic
{
    use ContainerAwareTrait;

    public function getJsonReport($orderid)
    {
        $newarr = $this->getReportByOrderid($orderid);
        if(empty($newarr)){
            return [];
        }
        //seat 座椅  checkbox 多选
        $newarr['seats'] = $this->getShowData($newarr['field_3150']['value'], "field_3150");
        //空调 air conditioning
        if(isset($newarr['field_3155']['value'])){
            $newarr['airconditions'] = $this->getShowData($newarr['field_3155']['value'], 'field_3155');
        }else{
            $newarr['airconditions'] = null;
        }
        //特殊车况
        $newarr['accidents'] = $this->getShowData($newarr['field_4140']['value'],'field_4140');

        //特殊车况
        //$newarr['accidents'] = $this->getDataMeteByField('field_4140', $newarr);
        //车辆颜色：白     天窗：普通       座椅：      空调：自动、后排独立出风
        $strreport = '';
        $strreport.= '车辆颜色:'.$newarr['field_3030']['value'].'&nbsp;|&nbsp;';
        $strreport.= '天窗:'.$newarr['field_3140']['value'].'&nbsp;|&nbsp;';
        if(isset($newarr['field_3150']['value']) && !empty($newarr['field_3150']['value'])){
            $strreport.= '座椅:'.implode(",",$newarr['field_3150']['value']).'&nbsp;|&nbsp;';
        }
        if(isset($newarr['field_3155']['value']) && !empty($newarr['field_3155']['value']) ){
            $strreport.= '空调:'.implode(",",$newarr['field_3155']['value']).'';
        }
        $newarr['strreport'] = $strreport;
        $strreport = '';
        $strreport.= '车辆颜色:'.$newarr['field_3030']['value'].'|';
        $strreport.= '天窗:'.$newarr['field_3140']['value'].'|';
        if(isset($newarr['field_3150']['value']) && !empty($newarr['field_3150']['value'])){
            $strreport.= '座椅:'.implode(",",$newarr['field_3150']['value']).'|';
        }
        if(isset($newarr['field_3155']['value']) && !empty($newarr['field_3155']['value']) ){
            $strreport.= '空调:'.implode(",",$newarr['field_3155']['value']).'';
        }
        $newarr['strreportpdf'] = $strreport;


        $newarr['orderid'] = $orderid;
        $newarr['reportnumber'] = "PG".date("Ymd")."$orderid";
        //显示值判断
        $newarr['field_4020']['value'] = $newarr['field_4020']['value']?$newarr['field_4020']['value']:0;
        $newarr['field_4030']['value'] = $newarr['field_4030']['value']?$newarr['field_4030']['value']:0;
        $newarr['field_4130']['value'] = $newarr['field_4130']['value']?$newarr['field_4130']['value']:0;
        $newarr['field_4040']['value'] = $newarr['field_4040']['value']?$newarr['field_4040']['value']:0;

        $newarr['field_4060']['value'] = $newarr['field_4060']['value']?$newarr['field_4060']['value']:"0%";
        $newarr['field_4110']['value'] = $newarr['field_4110']['value']?$newarr['field_4110']['value']:"0%";
        $newarr['field_4070']['value'] = $newarr['field_4070']['value']?$newarr['field_4070']['value']:"0%";
        $newarr['field_4120']['value'] = $newarr['field_4120']['value']?$newarr['field_4120']['value']:"0%";
        $newarr['field_4050']['value'] = $newarr['field_4050']['value']?$newarr['field_4050']['value']:"0%";
        $newarr['field_4080']['value'] = $newarr['field_4080']['value']?$newarr['field_4080']['value']:"0%";
        $newarr['field_4090']['value'] = $newarr['field_4090']['value']?$newarr['field_4090']['value']:"0%";
        $newarr['field_4100']['value'] = $newarr['field_4100']['value']?$newarr['field_4100']['value']:"0%";

        $newarr['field_4060']['value'] = $newarr['field_4060']['value'] != "%"?$newarr['field_4060']['value']:"0%";
        $newarr['field_4110']['value'] = $newarr['field_4110']['value'] != "%"?$newarr['field_4110']['value']:"0%";
        $newarr['field_4070']['value'] = $newarr['field_4070']['value'] != "%"?$newarr['field_4070']['value']:"0%";
        $newarr['field_4120']['value'] = $newarr['field_4120']['value'] != "%"?$newarr['field_4120']['value']:"0%";
        $newarr['field_4050']['value'] = $newarr['field_4050']['value'] != "%"?$newarr['field_4050']['value']:"0%";
        $newarr['field_4080']['value'] = $newarr['field_4080']['value'] != "%"?$newarr['field_4080']['value']:"0%";
        $newarr['field_4090']['value'] = $newarr['field_4090']['value'] != "%"?$newarr['field_4090']['value']:"0%";
        $newarr['field_4100']['value'] = $newarr['field_4100']['value'] != "%"?$newarr['field_4100']['value']:"0%";

        if ('拒绝放贷' === $newarr['field_result']['value']) {
            $newarr['refuseReason'] = $newarr['field_result']['options'];
        }

        return $newarr;
   }

   public function getJsonPrimaryReport($orderid)
    {
        $newarr = $this->getPrimaryReportByOrderid($orderid);
        if(empty($newarr)){
            return [];
        }
        //seat 座椅  checkbox 多选
        $newarr['seats'] = $this->getShowData($newarr['field_3150']['value'], "field_3150");
        //空调 air conditioning
        if(isset($newarr['field_3155']['value'])){
            $newarr['airconditions'] = $this->getShowData($newarr['field_3155']['value'], 'field_3155');
        }else{
            $newarr['airconditions'] = null;
        }
        //特殊车况
        $newarr['accidents'] = $this->getShowData($newarr['field_4140']['value'],'field_4140');

        //特殊车况
        //$newarr['accidents'] = $this->getDataMeteByField('field_4140', $newarr);
        //车辆颜色：白     天窗：普通       座椅：      空调：自动、后排独立出风
        $strreport = '';
        $strreport.= '车辆颜色:'.$newarr['field_3030']['value'].'&nbsp;|&nbsp;';
        $strreport.= '天窗:'.$newarr['field_3140']['value'].'&nbsp;|&nbsp;';
        if(isset($newarr['field_3150']['value']) && !empty($newarr['field_3150']['value'])){
            $strreport.= '座椅:'.implode(",",$newarr['field_3150']['value']).'&nbsp;|&nbsp;';
        }
        if(isset($newarr['field_3155']['value']) && !empty($newarr['field_3155']['value']) ){
            $strreport.= '空调:'.implode(",",$newarr['field_3155']['value']).'';
        }
        $newarr['strreport'] = $strreport;
        $strreport = '';
        $strreport.= '车辆颜色:'.$newarr['field_3030']['value'].'|';
        $strreport.= '天窗:'.$newarr['field_3140']['value'].'|';
        if(isset($newarr['field_3150']['value']) && !empty($newarr['field_3150']['value'])){
            $strreport.= '座椅:'.implode(",",$newarr['field_3150']['value']).'|';
        }
        if(isset($newarr['field_3155']['value']) && !empty($newarr['field_3155']['value']) ){
            $strreport.= '空调:'.implode(",",$newarr['field_3155']['value']).'';
        }
        $newarr['strreportpdf'] = $strreport;


        $newarr['orderid'] = $orderid;
        $newarr['reportnumber'] = "PG".date("Ymd")."$orderid";
        //显示值判断
        $newarr['field_4020']['value'] = $newarr['field_4020']['value']?$newarr['field_4020']['value']:0;
        $newarr['field_4030']['value'] = $newarr['field_4030']['value']?$newarr['field_4030']['value']:0;
        $newarr['field_4130']['value'] = $newarr['field_4130']['value']?$newarr['field_4130']['value']:0;
        $newarr['field_4040']['value'] = $newarr['field_4040']['value']?$newarr['field_4040']['value']:0;

        $newarr['field_4060']['value'] = $newarr['field_4060']['value']?$newarr['field_4060']['value']:"0%";
        $newarr['field_4110']['value'] = $newarr['field_4110']['value']?$newarr['field_4110']['value']:"0%";
        $newarr['field_4070']['value'] = $newarr['field_4070']['value']?$newarr['field_4070']['value']:"0%";
        $newarr['field_4120']['value'] = $newarr['field_4120']['value']?$newarr['field_4120']['value']:"0%";
        $newarr['field_4050']['value'] = $newarr['field_4050']['value']?$newarr['field_4050']['value']:"0%";
        $newarr['field_4080']['value'] = $newarr['field_4080']['value']?$newarr['field_4080']['value']:"0%";
        $newarr['field_4090']['value'] = $newarr['field_4090']['value']?$newarr['field_4090']['value']:"0%";
        $newarr['field_4100']['value'] = $newarr['field_4100']['value']?$newarr['field_4100']['value']:"0%";

        $newarr['field_4060']['value'] = $newarr['field_4060']['value'] != "%"?$newarr['field_4060']['value']:"0%";
        $newarr['field_4110']['value'] = $newarr['field_4110']['value'] != "%"?$newarr['field_4110']['value']:"0%";
        $newarr['field_4070']['value'] = $newarr['field_4070']['value'] != "%"?$newarr['field_4070']['value']:"0%";
        $newarr['field_4120']['value'] = $newarr['field_4120']['value'] != "%"?$newarr['field_4120']['value']:"0%";
        $newarr['field_4050']['value'] = $newarr['field_4050']['value'] != "%"?$newarr['field_4050']['value']:"0%";
        $newarr['field_4080']['value'] = $newarr['field_4080']['value'] != "%"?$newarr['field_4080']['value']:"0%";
        $newarr['field_4090']['value'] = $newarr['field_4090']['value'] != "%"?$newarr['field_4090']['value']:"0%";
        $newarr['field_4100']['value'] = $newarr['field_4100']['value'] != "%"?$newarr['field_4100']['value']:"0%";

        if ('拒绝放贷' === $newarr['field_result']['value']) {
            $newarr['refuseReason'] = $newarr['field_result']['options'];
        }

        return $newarr;
   }

    private function getReportByOrderid($orderid){
        $query = $this->getDoctrine()->getRepository('AppBundle:Order');
        $order = $query->find($orderid);
        if(!$order || !$order->getReport()){
            return [];
        }
        $reportOp = $order->getReport();
        $arr = $reportOp->getReport();

        if(empty($arr) || !$arr){
            return [];
        }
        $newarr = [];
        $newarr['examer'] = $reportOp->getExamer()? $reportOp->getExamer()->getName(): "";
        $newarr['examedat'] = $reportOp->getExamedAt()? $reportOp->getExamedAt()->format("Y年m月d日"): "";
        $newarr['reportstatus'] = $reportOp->getStatus();
        $newarr['showordernum'] = $order->getOrderNo();
        $newarr['csbResults'] = $reportOp->getCsbResults();
        //以海涛的MetaData 数据字段信息对 report里面的字段信息进行过滤
        $checkArr = [];
        foreach($arr as $k=>$v){
            $checkArr[] = $k;
            $newarr[$k]['value'] = $v['value'];
            $newarr[$k]['options'] = '';
            // 略有修改，主要是为了获取拒绝原因字段
            if(isset($v['options']) && !empty($v['options']) && isset($v['options']['textarea'])){
                $newarr[$k]['options'] = $v['options']['textarea'];
            }
        }
        //TODO 过滤没有字段
        $metadatas = $this->getDataMeteField();
        foreach($metadatas as $metadata){
            if(!in_array($metadata, $checkArr)){
                $newarr[$metadata]['value'] = "";
                $newarr[$metadata]['options'] = '';
            }
        }
        return $newarr;
    }

    private function getPrimaryReportByOrderid($orderid)
    {
        $query = $this->getDoctrine()->getRepository('AppBundle:Order');
        $order = $query->find($orderid);
        if(!$order || !$order->getReport()){
            return [];
        }
        $reportOp = $order->getReport();
        $arr = $reportOp->getPrimaryReport();

        if(empty($arr) || !$arr){
            return [];
        }
        $newarr = [];
        $newarr['examer'] = $reportOp->getExamer()? $reportOp->getExamer()->getName(): "";
        $newarr['examedat'] = $reportOp->getExamedAt()? $reportOp->getExamedAt()->format("Y年m月d日"): "";
        $newarr['reportstatus'] = $reportOp->getStatus();
        $newarr['showordernum'] = $order->getOrderNo();
        $newarr['csbResults'] = $reportOp->getCsbResults();
        //以海涛的MetaData 数据字段信息对 report里面的字段信息进行过滤
        $checkArr = [];
        foreach($arr as $k=>$v){
            $checkArr[] = $k;
            $newarr[$k]['value'] = $v['value'];
            $newarr[$k]['options'] = '';
            // 略有修改，主要是为了获取拒绝原因字段
            if(isset($v['options']) && !empty($v['options']) && isset($v['options']['textarea'])){
                $newarr[$k]['options'] = $v['options']['textarea'];
            }
        }
        //TODO 过滤没有字段
        $metadatas = $this->getDataMeteField();
        foreach($metadatas as $metadata){
            if(!in_array($metadata, $checkArr)){
                $newarr[$metadata]['value'] = "";
                $newarr[$metadata]['options'] = '';
            }
        }
        return $newarr;
    }

   private function getShowData($values, $field)
   {
        if(empty($values)){
            return null;
        }
        $newarr = [];
        foreach($values as $k=>$value){
            $i = ceil(($k+1)/5);
            $j = $i%2;
            if($j == 1){
                $newarr[] = ['value'=>$value,'key'=>1];
            }else{
                $newarr[] = ['value'=>$value,'key'=>2];
            }
        }
        $fields = ['field_3150', 'field_4140'];
        $last = null;
        if(in_array($field, $fields)){
            $num = count($newarr);
            $j = $num%5;
            $i = 5 - $j;
            if($i<5){
                $last['key'] = $newarr[$num-1]['key'];
                for($m=0;$m<$i;$m++){
                    $last['value'][]= "div";
                }
            }

        $ret = [];
        $ret['count'] = 0;
        $ret['newarr'] = $newarr;
        $ret['last'] = $last;
        $ret['count'] = ceil(count($newarr)/5);
        $newarr = $ret;
        }
        return $newarr;
   }

    private function getDataMeteField()
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $metadatasArray = $mm->getMetadata4CheckArray();
        $dataMeteField = [];
        foreach($metadatasArray as $metadatas){
            foreach($metadatas as $fieldValue){
                $dataMeteField[] = $fieldValue->key;
            }
        }
        return $dataMeteField;

    }
}
