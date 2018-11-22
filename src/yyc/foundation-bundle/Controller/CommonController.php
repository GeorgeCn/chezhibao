<?php

namespace YYC\FoundationBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use YYC\FoundationBundle\Controller\Base\AbstractController;
use YYC\FoundationBundle\Controller\Base\YYC_Base_Module;
use YYC\FoundationBundle\Entity\Maintain;
use YYC\FoundationBundle\Entity\Insurance;

/**
 * 暂时的公共路由
 *
 */
class CommonController extends AbstractController
{

    /**
     * 详情页面
     * @param $request
     * @param $maintain
     *
     * @return Response
     */
    public function showAction(Request $request, Maintain $maintain)
    {
        //检测是否登陆
        if (is_null($this->getUser())) {
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        //获取vin的id
        $vin = $request->get('id');
        //获取report id
        $report_id = $request->get('report');
        //根据report id 获取order信息
        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->findOneByReport($report_id);
        if ($maintain->getResults()) {
            //获取维保来源 ( 1: 大圣来了  2: 车鉴定  3: 查博士  4: 聚合数据  5: 蚂蚁女王 )
            $source = $maintain->getSupplierType();
            //根据来源选择不同模版
            switch ($source) {
                case 1:
                    $result = YYC_Base_Module::_getInstance()->show('dsll', $maintain);
                    break;
                case 2:
                    $result = YYC_Base_Module::_getInstance()->show('cjd', $maintain);
                    break;
                case 3:
                    $result = YYC_Base_Module::_getInstance()->show('cbs', $maintain);
                    break;
                case 4:
                    $result = YYC_Base_Module::_getInstance()->show('juhe', $maintain);
                    break;
                case 5:
                    $result = YYC_Base_Module::_getInstance()->show('antQueen', $maintain);
                    break;
                default;
                    $result = '数据源不存在';//缺少通用模版(null不现实数据)
            }
            $result = $result->getContent();
            //格式化数据
            $maintain = $this->maintainDataReconsitution($result);
            $maintain['showordernum'] = $order->getOrderNo();
            $maintain['vin'] = $vin;
            $maintain['id'] = $report_id;
            $keyWords = ["纵梁", "避震座", "防火墙", "行李箱", "底板", "后窗台", "车顶", "车壳", "A柱", "B柱", "C柱", "D柱", "上边梁", "下边梁", "水箱框架", "龙门架", "气囊", "安全带", "电脑板", "后翼子板", "后叶子板", "地毯", "仪表台", "后围板", "大底边", "座椅", "水泡", "火烧", "翻车", "烧焊", "整形", "切割", "拆装", "发动机", "变速箱", "大修"];
            foreach ($keyWords as $v) {
                $arrayWords[$v] = "<b style='color:red'>".$v."</b>"; 
            } 
            $maintain['keyWords'] = $arrayWords;

            return $this->render('YYCFoundationBundle:hpl:maintain-record.html.twig', $maintain);
        } else {
            return new Response('该记录平台商没有返回结果给我们！');
        }
    }


    /**
     * hpl 使用
     * @param $request
     * @return Response
     */
    public function synchplAction(Request $request)
    {
        $vin = $request->get('vin');
        $id = $request->get('maintain_id');
        $type = $request->get('type');
        $tmp = $this->getDoctrine()->getRepository('YYCFoundationBundle:Maintain')->findRecentlyByVin($vin);
        if ($tmp) {
            $encoder = new JsonEncoder();
            $normalizer = new GetSetMethodNormalizer();
            //格式化时间的显示
            $callback = function ($dateTime) {
                return $dateTime instanceof \DateTime
                    ? $dateTime->format('Y-m-d H:i:s')
                    : '';
            };
            $normalizer->setCallbacks(array('createdAt' => $callback));
            $serializer = new Serializer(array($normalizer), array($encoder));
            $data = $serializer->serialize($tmp, 'json');
            $data = json_decode($data);
            //type == 2 更新 hpl report表的maintain_id
            if ($type == 2) {
                $list = [];
                foreach ($data as $k => $v) {
                    $list[$k] = $v->status;
                }
                //成功记录的下标
                $suc = array_search('1', $list);
                if ($suc === false) {//查询中
                    //产品又该需求(只有查询成功入库,查询中和查询失败不入库)
                    //$wait = array_search('0', $list);
                    //if ($wait === false) {
                    //    $maintain_obj = false;
                    //} else {
                    //    $maintain_obj = $data[$wait];
                    //}
                    $maintain_obj = false;
                } else {
                    $maintain_obj = $data[$suc];
                }
                if ($maintain_obj) {
                    $maintain_id = $maintain_obj->id;
                    if ($maintain_id > 0) {
                        $report = $this->getDoctrine()->getRepository('AppBundle:Report')->find($id);
                        if ($report->getMaintain() != $maintain_id) {
                            $report->setMaintain($maintain_id);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($report);
                            $em->flush();
                        }
                    }
                }
            }
            return new JsonResponse($data);
        } else {
            return new JsonResponse(array('success' => false, 'msg' => '未查询过维修记录'));
        }
    }


    /**
     * 维修报告根据来源重构数据
     * @param $originData
     * @return Array
     */
    public function maintainDataReconsitution($originData)
    {
        $jsonData = json_decode($originData);

        $decollatorBr = "<br />";

        if (!isset($jsonData)) {
            $maintain['hadReport'] = false;
        } else {
            $maintain['originType'] = $jsonData->maintain_type;
            $maintain['hadReport'] = true;
            $resultDescription = $jsonData->report->result_description;
            $resultDescriptionArr = array();

            $itemMaintain = $jsonData->maintenance;

            switch ($maintain['originType']) {
                case 1://大圣来了
                    $resultDescription = nl2br($resultDescription);
                    $resultDescriptionArr = explode($decollatorBr, $resultDescription);
                    foreach ($resultDescriptionArr as $k1 => $v1) {
                        $resultDescriptionArr[$k1] = trim($v1);
                    }
                    $itemMaintain = json_decode($jsonData->maintenance->result_content);
                    if ($itemMaintain) {
                        foreach ($itemMaintain as $k2 => $v2) {
                            if (is_string($itemMaintain[$k2]->images)) {
                                $itemMaintain[$k2]->images = explode(',', $itemMaintain[$k2]->images);
                            }
                        }
                    }
                    break;
                case 2://车鉴定
                    if (is_object($resultDescription)) {
                        $sd = "结构部件：" . ($resultDescription->sd ? '异常' : '正常');
                        $ab = "安全气囊：" . ($resultDescription->ab ? '异常' : '正常');
                        $mi = "里程表：" . ($resultDescription->mi ? '异常' : '正常');
                        $ronum = "维保次数：" . $resultDescription->ronum;
                        $mile = "最大里程：" . $resultDescription->mile . '公里';
                        $resultDescription = $sd . $decollatorBr . $ab . $decollatorBr . $mi . $decollatorBr . $ronum . $decollatorBr . $mile;
                    }

                    $resultDescriptionArr = explode($decollatorBr, $resultDescription);

                    if ($itemMaintain) {
                        $itemMaintain = array_reverse($itemMaintain);
                        foreach ($itemMaintain as $k3 => $v3) {
                            $itemMaintain[$k3]->content = str_replace("&nbsp;", " ", $itemMaintain[$k3]->content);
                            $itemMaintain[$k3]->material = str_replace("&nbsp;", " ", $itemMaintain[$k3]->material);
                        }
                    }
                    break;
                case 3:
                    $carAccidentFlag = "是否事故：" . ($resultDescription->carAccidentFlag ? '是' : '否');
                    $carFireFlag = "是否火烧：" . ($resultDescription->carFireFlag ? '是' : '否');
                    $carWaterFlag = "是否水泡：" . ($resultDescription->carWaterFlag ? '是' : '否');
                    $carComponentRecordsFlag = "重要组成部件是否有维修：" . ($resultDescription->carComponentRecordsFlag ? '是' : '否');
                    $carConstructRecordsFlag = "结构件是否有维修：" . ($resultDescription->carConstructRecordsFlag ? '是' : '否');
                    $carOutsideRecordsFlag = "外观覆盖件是否有维修：" . ($resultDescription->carOutsideRecordsFlag ? '是' : '否');
                    $mileageIsNormalFlag = "公里数是否正常：" . ($resultDescription->mileageIsNormalFlag ? '是' : '否');
                    $mainTainTimes = "年平均保养次数：" . $resultDescription->mainTainTimes;
                    $mileageEveryYear = "年平均行驶公里数：" . $resultDescription->mileageEveryYear;

                    array_push($resultDescriptionArr,$carAccidentFlag,$carFireFlag,$carWaterFlag,$carComponentRecordsFlag,$carConstructRecordsFlag,$carOutsideRecordsFlag,$mileageIsNormalFlag,$mainTainTimes,$mileageEveryYear);

                    foreach ($itemMaintain as $k5 => $v5) {
                        if($itemMaintain->$k5){
                            $itemMaintain->$k5 = array_reverse($itemMaintain->$k5);
                        }
                    }

                    break;

                case 4://聚合数据
                    $maintain['imgs'] = explode(',', $jsonData->imgs);
                    $maintain['desc'] = implode('##',$resultDescription);
                    break;

                case 5://蚂蚁女王
                    if ($itemMaintain) {
                        $itemMaintain = json_decode($itemMaintain);
                        foreach ($itemMaintain as $k => $v) {
                            $itemMaintain[$k]->detail = str_replace("&nbsp;", " ", $itemMaintain[$k]->detail);
                            $itemMaintain[$k]->cailiao = str_replace("&nbsp;", " ", $itemMaintain[$k]->cailiao);
                        }
                    }
                    $maintain['imgs'] = explode(',', $jsonData->imgs);
                    $resultDescriptionArr = $resultDescription;
                    break;

                default:
                    $maintain['hadReport'] = false;//多此一举
            }
        }

        if ($maintain['hadReport']) {
            $maintain['basic'] = $jsonData->basic;
            $maintain['resume'] = $resultDescriptionArr;
            $maintain['record'] = $itemMaintain;
        }

        return $maintain;
    }

    /**
     * 检查vin码的状态判断是否已有结果返回
     */
    public function checkStatusAction(Request $request)
    {
        $vin = $request->query->get('vin');
        $insurance = $this->getDoctrine()->getRepository('AppBundle:Insurance')->findOneBy(array('vin' => $vin), array('createdAt' => 'DESC'));

        if ($insurance) {
            if (Insurance::STATUS_WAIT === $insurance->getStatus()) {
                return new JsonResponse(array('success' => false));
            } else {
                return new JsonResponse(array('success' => true));
            }
        } else {
            return new JsonResponse(array('success' => false));
        }
    }
}