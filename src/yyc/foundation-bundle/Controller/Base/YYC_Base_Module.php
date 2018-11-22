<?php
namespace YYC\FoundationBundle\Controller\Base;

/**
 * 模板选择器
 *
 * 此类不允许外部调用和继承
 *
 * 根据不同维保来源的数据结构分配出制定的数据结构  - 根据后期结构复杂情况定制结构验证类 YYC_Base_Module_Verify_Utils 结构 ，参数 ，参数类型等验证类
 */
use Symfony\Component\HttpFoundation\JsonResponse;

final class YYC_Base_Module
{

    /**
     * 类的实例
     *
     * @var string
     */
    private static $_instance = null;

    /**
     * 获取单例
     *
     * @return YYC_Base_Module
     */
    public static function _getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new YYC_Base_Module();
        }
        return self::$_instance;
    }

    /**
     * 模版选择
     *
     * @param from
     * @param value
     * @return JsonResponse
     */
    public function show($from, $value)
    {
        if (is_null(self::$_instance)) {
            self::_getInstance();
        }
        return new JsonResponse(self::$_instance->$from($value));
    }

    /**
     * 大圣来了
     * @param $maintain
     * @return array
     */
    public function dsll($maintain)
    {
        //基本信息
        $list['maintain_type'] = $maintain->getSupplierType();// 来源
        $list['basic']['vin'] = $maintain->getVin();// vin 码
        $list['basic']['brandName'] = $maintain->getResults()['car_brand'];// 品牌
        $list['basic']['last_time_to_shop'] = $maintain->getResults()['last_time_to_shop'];//最后入店时间
        $list['basic']['total_mileage'] = $maintain->getResults()['total_mileage'];//最后入店公里数
        $list['basic']['number_of_accidents'] = $maintain->getResults()['number_of_accidents'];//事故次数
        $list['report']['result_description'] = $maintain->getResults()['result_description'];//报告简述
        //result_description
        $list['maintenance'] = $maintain->getResults();
        return $list;
    }

    /**
     * 车鉴定
     * @param $maintain
     * @return array
     */
    public function cjd($maintain)
    {
        //基本信息
        $list['maintain_type'] = $maintain->getSupplierType();// 来源
        $list['basic']['vin'] = $maintain->getVin();// vin 码
        $list['basic']['brandName'] = $maintain->getResults()['basic']['brand'];// 品牌
        //最后入店时间
        $list['basic']['last_time_to_shop'] = "在保养记录里查看";//车鉴定(没有最后入店时间)
        //最后入店公里数
        $list['basic']['total_mileage'] = $maintain->getResults()['resume']['mile'];
        //事故次数
        $list['basic']['number_of_accidents'] = "在保养记录里查看";//车鉴定没有
        //报告简述
        $list['report']['result_description'] = $maintain->getResults()['resume'];
        //result_description
        $list['maintenance'] = $maintain->getResults()['reportJson'];
        return $list;
    }

    /**
     * 查博士
     * @param $maintain
     * @return array
     */
    public function cbs($maintain)
    {
        $obj = $maintain->getResults();
        //基本信息
        $list['maintain_type'] = $maintain->getSupplierType();// 来源
        $list['basic']['vin'] = $maintain->getVin();// vin 码
        if ($obj) {
            $list['basic']['brandName'] = $obj['manufacturer'];// 品牌 (生产厂商)
            //最后入店时间  lastMainTainTime(最后一次保养时间)  lastRepairTime(最后一次维修时间)
            $list['basic']['last_time_to_shop'] = $obj['lastRepairTime'];//最后一次维修时间
            //最后入店公里数
            $list['basic']['total_mileage'] = "在保养记录里查看"; //如果为0 (查博士没有估出来)
            //事故次数
            $list['basic']['number_of_accidents'] = "在保养记录里查看";//carAccidentFlag 是否事故(0-否 1-是)
            //报告简述
            $list['report']['result_description'] =
                [
                    'carAccidentFlag' => isset($obj['carAccidentFlag']) ? $obj['carAccidentFlag'] : 0,//是否事故 (字段不存在)
                    'carFireFlag' => $obj['carFireFlag'],//是否火烧
                    'carWaterFlag' => $obj['carWaterFlag'],//是否水泡
                    'carComponentRecordsFlag' => $obj['carComponentRecordsFlag'],//重要组成件是否有维修
                    'carConstructRecordsFlag' => $obj['carConstructRecordsFlag'],//结构件是否有维修
                    'carOutsideRecordsFlag' => $obj['carOutsideRecordsFlag'],//外观覆盖件是否有维修
                    'mileageIsNormalFlag' => $obj['mileageIsNormalFlag'],//公里数是否正常
                    'mainTainTimes' => $obj['mainTainTimes'],//每年保养次数
                    'mileageEveryYear' => $obj['mileageEveryYear']//每年行驶公里数
                ];
            //result_description (维修保养记录)
            //维修保养分个模块 1:结构件详情维修记录  2: 重要组成部件详情维修记录 3: 外观覆盖件详情维修记录 4: 该车所有的详情维修记录
            $list['maintenance'] =
                [
                    'constructAnalyzeRepairRecords' => $obj['constructAnalyzeRepairRecords'],//结构详细维修记录
                    'componentAnalyzeRepairRecords' => $obj['componentAnalyzeRepairRecords'],//重要组成部件详细维修记录
                    'outsideAnalyzeRepairRecords' => $obj['outsideAnalyzeRepairRecords'],//外观覆盖件详细维修记录
                    'normalRepairRecords' => $obj['normalRepairRecords'],//该车所有的详细维修记录(普通报告)
                ];
        }
        return $list;
    }

    /**
     * 聚合数据
     * @param $maintain
     * @return array
     */
    public function juhe($maintain)
    {
        //基本信息
        $list['maintain_type'] = $maintain->getSupplierType();// 来源
        $list['basic']['vin'] = $maintain->getVin();// vin 码
        $list['basic']['brandName'] = $maintain->getResults()['car_brand'];// 品牌
        //最后入店时间
        $list['basic']['last_time_to_shop'] = $maintain->getResults()['last_time_to_shop'];
        //最后入店公里数
        $list['basic']['total_mileage'] = $maintain->getResults()['total_mileage'];
        //事故次数（聚合数据称为异常次数）
        $list['basic']['number_of_accidents'] = $maintain->getResults()['number_of_accidents'];
        //报告简述
        $list['report']['result_description'] = $maintain->getResults()['result_report'];
        //result_description(维修保养记录)
        $list['maintenance'] = $maintain->getResults()['result_content'];
        $list['imgs'] = $maintain->getResults()['result_images'];

        return $list;
    }

    /**
     * 蚂蚁女王
     * @param $maintain
     * @return array
     */
    public function antQueen($maintain)
    {
        //基本信息
        $list['maintain_type'] = $maintain->getSupplierType();// 来源
        $list['basic']['vin'] = $maintain->getVin();// vin 码
        $list['basic']['brandName'] = $maintain->getResults()['car_brand'];// 品牌
        $list['basic']['last_time_to_shop'] = $maintain->getResults()['last_time_to_shop'];//最后入店时间
        $list['basic']['total_mileage'] = $maintain->getResults()['total_mileage'];//最后入店公里数
        $list['basic']['number_of_accidents'] = $maintain->getResults()['number_of_accidents'];//事故次数

        $desc = [];
        $tmps = json_decode($maintain->getResults()['car_status'], true);
        foreach ($tmps as $tmp) {
            $desc[] = $tmp['title'].": ".$tmp['desc'];
        }

        $list['report']['result_description'] = $desc;//报告简述
        //result_description
        $list['maintenance'] = $maintain->getResults()['query_text'];
        $list['imgs'] = $maintain->getResults()['result_images'];

        return $list;
    }
}