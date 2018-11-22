<?php

namespace YYC\FoundationBundle\Third;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 聚合数据
 * https://www.juhe.cn/docs/api/id/270/aid/1063 汽车维修保养记录
 * https://www.juhe.cn/docs/api/id/282 保险理赔查询
 */
class Juhe
{
    const CLAIMS_QUERY_URL = "http://v.juhe.cn/claims/query.php";
    const MAINTENCE_SUBMITORDER_URL = "http://v.juhe.cn/maintenance/submitOrder.php";
    const MAINTENCE_URLCONCFIG_URL = "http://v.juhe.cn/maintenance/urlConfig.php";
    const MAINTENCE_CHECK_URL = "http://v.juhe.cn/maintenance/check.php";
    const MAINTENCE_DETAIL_URL = "http://v.juhe.cn/maintenance/detail.php";

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function getClaimsKey()
    {
        return $this->container->getParameter('yyc_foundation.juhe.claims_key');
    }

    private function getMaintenceKey()
    {
        return $this->container->getParameter('yyc_foundation.juhe.maintence_key');
    }

    public function queryClaims($vin, $plate)
    {
        $curl = $this->container->get('util.curl_helper');
        $params["licenseNo"] = $vin;
        $params["frameNo"] = $plate;
        $params["key"] = $this->getClaimsKey();
        return $this->mockStub(self::CLAIMS_QUERY_URL, $params);
    }

    public function submitMaintenanceOrder($vin)
    {
        $curl = $this->container->get('util.curl_helper');
        $params["vin"] = $vin;
        $params["key"] = $this->getMaintenceKey();
        return $this->mockStub(self::MAINTENCE_SUBMITORDER_URL, $params);
    }

    public function configUrl($url)
    {
        $curl = $this->container->get('util.curl_helper');
        $params["url"] = $url;
        $params["key"] = $this->getMaintenceKey();
        return $this->mockStub(self::MAINTENCE_URLCONCFIG_URL, $params);
    }

    public function checkMaintenance($vin)
    {
        $curl = $this->container->get('util.curl_helper');
        $params["vin"] = $vin;
        $params["key"] = $this->getMaintenceKey();
        return $this->mockStub(self::MAINTENCE_CHECK_URL, $params);
    }

    public function getMaintenanceDetail($orderId, $vin)
    {
        $curl = $this->container->get('util.curl_helper');
        $params["vin"] = $vin;
        $params["orderId"] = $orderId;
        $params["key"] = $this->getMaintenceKey();
        return $this->mockStub(self::MAINTENCE_DETAIL_URL, $params);
    }

    private function mockStub($url, $params)
    {
        $curl = $this->container->get('util.curl_helper');
        if ($params["key"] != "debug") {
            $ret = $curl->get(self::MAINTENCE_CHECK_URL, $params);
            if ($ret === false || $ret["error_code"] != 0) {
                return false;
            }
            return $ret["result"];
        }

        switch ($url) {
            case self::CLAIMS_QUERY_URL:
                $json = '{
                        "reason": "success",
                        "result": {
                            "summaryData": {
                                "claimMoney": 923600,
                                "repairMoney": 520000,
                                "claimCount": 3,
                                "repairCount": 10,
                                "renewCount": 1,
                                "renewMoney": 403600
                            },
                            "carClaimRecords": [
                                {
                                    "claimDetails": [
                                        {
                                            "itemName": "后保险杠(全喷)",
                                            "itemType": "维修",
                                            "itemAmount": "70000"
                                        },
                                        {
                                            "itemName": "后保险杠修复(小)",
                                            "itemType": "维修",
                                            "itemAmount": "20000"
                                        }
                                    ],
                                    "licenseNo": "闽A****",
                                    "vehicleModel": "奔驰BENZ S300L轿车",
                                    "frameNo": "**************1415",
                                    "otherAmount": "0",
                                    "repairAmount": "90000",
                                    "renewalAmount": "0",
                                    "dangerTime": "2017-01-24 14:14:00",
                                    "damageMoney": "90000"
                                },
                                {
                                    "claimDetails": [
                                        {
                                            "itemName": "右前门(全喷)",
                                            "itemType": "维修",
                                            "itemAmount": "70000"
                                        },
                                        {
                                            "itemName": "右后门(全喷)",
                                            "itemType": "维修",
                                            "itemAmount": "70000"
                                        },
                                        {
                                            "itemName": "右前叶子板(全喷)",
                                            "itemType": "维修",
                                            "itemAmount": "70000"
                                        },
                                        {
                                            "itemName": "右后叶子板(全喷)",
                                            "itemType": "维修",
                                            "itemAmount": "70000"
                                        },
                                        {
                                            "itemName": "右前车门壳拆装",
                                            "itemType": "维修",
                                            "itemAmount": "5000"
                                        },
                                        {
                                            "itemName": "右后车门壳拆装",
                                            "itemType": "维修",
                                            "itemAmount": "5000"
                                        }
                                    ],
                                    "licenseNo": "闽*****",
                                    "vehicleModel": "奔驰BENZ S300L轿车",
                                    "frameNo": "**************1415",
                                    "otherAmount": "0",
                                    "repairAmount": "290000",
                                    "renewalAmount": "0",
                                    "dangerTime": "2017-01-14 13:29:00",
                                    "damageMoney": "290000"
                                },
                                {
                                    "claimDetails": [
                                        {
                                            "itemName": "前杠喷漆",
                                            "itemType": "维修",
                                            "itemAmount": "70000"
                                        },
                                        {
                                            "itemName": "机盖喷漆",
                                            "itemType": "维修",
                                            "itemAmount": "70000"
                                        },
                                        {
                                            "itemName": "中网（中）",
                                            "itemType": "换件",
                                            "itemAmount": "403600"
                                        }
                                    ],
                                    "licenseNo": "闽******",
                                    "vehicleModel": "奔驰BENZ S300L轿车",
                                    "frameNo": "**************1415",
                                    "otherAmount": "0",
                                    "repairAmount": "140000",
                                    "renewalAmount": "403600",
                                    "dangerTime": "2017-01-14 12:43:00",
                                    "damageMoney": "543600"
                                }
                            ]
                        },
                        "error_code": 0
                }';
                break;
            case self::MAINTENCE_SUBMITORDER_URL:
                $json = '{
                  "reason": "success",
                  "result": {
                    "order_id": "525959001495432662",
                    "vin": "LDC661T26G3502916"
                  },
                  "error_code": 0
                }';
                break;
            case self::MAINTENCE_URLCONCFIG_URL:
                $json = '{
                    "reason": "success",
                    "result": "ok",
                    "error_code": 0
                }';
                break;
            case self::MAINTENCE_CHECK_URL:
                $json = '{
                  "reason": "success",
                  "result": {
                    "canQuery": true
                  },
                  "error_code": 0
                }';
                break;
            case self::MAINTENCE_DETAIL_URL:
                $json = '{
                    "reason": "success",
                    "result": {
                        "order_id": "525959001495432662",
                        "notify_time": "2017-06-29 17:49",
                        "total_mileage": 56332,
                        "number_of_accidents": 1,
                        "car_brand": "保时捷",
                        "result_status": "QUERY_SUCCESS",
                        "result_content": [
                            {
                                "content": "新车检测 .;新车PDI新车PDI检测已完成;",
                                "date": "2013-02-27",
                                "materal": null,
                                "mileage": 13,
                                "remark": null
                            },
                            {
                                "content": "前裙板 已安装的;尾部装饰件 已安装的;加装前后底护板加装前后底护板已完成;",
                                "date": "2013-03-12",
                                "materal": ";盖 不锈钢:1;盖:1;",
                                "mileage": 510,
                                "remark": null
                            },
                            {
                                "content": "更换机油服务 .;2 雨刮片 已拆下并重新安装;应客户要求更换机油服务换油服务已完成;客户反映雨刮有异响雨刮片吱吱响雨刮片变形引起更换雨刮片已完成;",
                                "date": "2013-07-12",
                                "materal": ";滤芯:1;密封环:1;螺塞:1;机油:8;雨刷器刮片:1;",
                                "mileage": 8546,
                                "remark": null
                            },
                            {
                                "content": "开关控制台 已拆下并重新安装;据客户反应:车辆前部的控制开关破损更换.开关控制台已更换，功能恢复正常;",
                                "date": "2013-09-07",
                                "materal": ";控制部件:1;",
                                "mileage": 10222,
                                "remark": null
                            },
                            {
                                "content": "更换机油服务 .;电动车窗开关 已拆下并重新安装;整车-概述://车身-外侧设备://;应客户要求更换机油服务机油服务已更换完成;客户反映右后门玻璃升降开关损坏Confirmthefaultwasexist.Afterchecking,thereisnodamagedtracesonthisswitches.Itwastheswitches&#039;surfacefault.Theproblemwassolvedafterreplacingthispowerwindowswitch.Replacednewswitchandcleanthefault.;鉴于该车先前已经执行过保养服务，现对WD60进行0申报处理。;",
                                "date": "2014-02-07",
                                "materal": ";Mobil Oil 1 0W40 DR208L:8;节气门清洗剂:1;添加剂:1;螺塞:1;密封环:1;滤芯:1;开关 暗黑/高光铬:1;",
                                "mileage": 18575,
                                "remark": null
                            },
                            {
                                "content": "中等保养 .;中央出风口，后部 已拆下并重新安装;整车-概述://空调://;应客户要求:执行机油保养服务.中等保养完成;客户反应:车窗出风口损坏Confirmthefaultwasexist.Checkinsideandfoundthefixedplacearedropoff.TheairventilateadjusterisbrokenandcannotrepairRepairanewairnoozlethensolovedthisproblem.;",
                                "date": "2014-07-19",
                                "materal": ";Mobil Oil 1 0W40 DR208L:8;节气门清洗剂:1;添加剂:1;螺塞:1;密封环:1;滤芯:1;出风口 暗黑/电镀银:1;",
                                "mileage": 28116,
                                "remark": null
                            },
                            {
                                "content": "更换机油服务 .;细燃油滤网 已拆下并重新安装;整车-概述:更换机油服务.//汽油,排气,发动机电子://;换油服务，更换汽油滤芯更换机油服务已完成，细燃油滤芯已更换完成;",
                                "date": "2014-10-29",
                                "materal": ";Mobil Oil 1 0W40 DR208L:8;节气门清洗剂:1;螺塞:1;密封环:1;滤芯:1;法兰:1;密封环:1;",
                                "mileage": 38274,
                                "remark": null
                            },
                            {
                                "content": "执行车间活动AG02;RecallAction://;done֐¤;",
                                "date": "2017-06-08",
                                "materal": null,
                                "mileage": 56332,
                                "remark": null
                            }
                        ],
                        "ext_info": [],
                        "result_report": [
                            "无水浸事故",
                            "无火烧事故",
                            "动力总成、电气及安全设备记录正常",
                            "外观件及钣金件记录异常",
                            "车身结构件记录正常",
                            "里程表记录正常"
                        ],
                        "last_time_to_shop": "2017-06-08",
                        "result_description": [
                            {
                                "date": "2016-0-09",
                                "content": "项目：钣金拆检；机电拆检；更新右前大灯；更新机盖；更新右前叶；拆装右侧倒车镜壳；右侧倒车镜油漆；前保油漆；机盖油漆；右前叶油漆；右侧围油漆；材料：前保险杠；引擎盖；大灯；前叶子板；前挡风玻璃；"
                            },
                            {
                                "date": "2012-05-05",
                                "content": "项目：后保拆检报料；后保烤漆；"
                            }
                        ],
                        "vin": "WP1AG2926DLA*****",
                        "result_images": "http://pic.******.com2af2.jpg,http://pic.******.com/m40e17b2.jpg,http://pic.dad84.jpg"
                    },
                    "error_code": 0
                }';
                break;
        }

        return json_decode($json, true)["result"];
    }
}
