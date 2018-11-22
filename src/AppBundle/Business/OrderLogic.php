<?php

namespace AppBundle\Business;

use AppBundle\Entity\Config;
use AppBundle\Entity\Order;
use AppBundle\Entity\Report;
use AppBundle\Event\HplEvents;
use AppBundle\Event\OrderSubmitEvent;
use AppBundle\Traits\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Model\Metadata;

class OrderLogic
{
    use ContainerAwareTrait;

    public function createOrder($user)
    {
        $em = $this->getDoctrineManager();
        $order = new Order();
        $order->setLoadOfficer($user);
        $em->persist($order);
        $em->flush();
        return $order;
    }

    public function updateOrder($order, $posts, $submit = false, $createNew = false)
    {
        $em = $this->getDoctrineManager();
        if (isset($posts["valuation"])) {
            $order->setValuation($posts["valuation"]);
        }
        if (isset($posts['businessNumber'])) {
            $order->setBusinessNumber($posts['businessNumber']);
        }
        if (isset($posts["remark"])) {
            $order->setRemark($posts["remark"]);
        }
        if (isset($posts["longitude"])) {
            $order->setLongitude($posts["longitude"]);
        }
        if (isset($posts["latitude"])) {
            $order->setLatitude($posts["latitude"]);
        }
        if (isset($posts["extras"]) && $posts["extras"]) {
            $extras = $posts["extras"];
            foreach ($extras as $key => $value) {
                $method = 'set'.ucfirst($key);
                $order->$method($value);
            }
        }

        if (isset($posts["parentId"]) && $posts["parentId"]) {
            $parentOrder = $this->getRepo('AppBundle:Order')->find($posts['parentId']);
            if ($parentOrder) {
                // 将父单子的允许复制次数-1
                $allowCopyTimes = $parentOrder->getAllowCopyTimes();
                $parentOrder->setAllowCopyTimes($allowCopyTimes - 1);

                $order->setParent($parentOrder);
            }
        }

        $bf = $this->get('app.business_factory');
        $companyName = $order->getLoadOfficer()->getAgencyRels()[0]->getCompany()->getCompany();
        $company = $this->getRepo('AppBundle:Config')->findOneBy(['company' => $companyName]);

        $agencyName = $order->getLoadOfficer()->getAgencyRels()[0]->getAgency()->getName();
        $agencyCode = $order->getLoadOfficer()->getAgencyRels()[0]->getAgency()->getCode();
        $agencyId = $order->getLoadOfficer()->getAgencyRels()[0]->getAgency()->getId();

        if ($order->getCompany()) {
            $companyName = $order->getCompany()->getCompany();
        } else {
            $order->setCompany($company);
        }

        // 如果传有公司值用传的值
        if (isset($posts['companyId']) && $posts['companyId']) {
            $company = $this->getRepo('AppBundle:Config')->find($posts['companyId']);
            if ($company) {
                $order->setCompany($company);
                $companyName = $company->getCompany();
                $agencyRel = $this->getRepo('AppBundle:AgencyRel')->findOneBy(['user' => $order->getLoadOfficer(), 'company' => $company]);
                if ($agencyRel) {
                    $agency = $agencyRel->getAgency();
                    if ($agency) {
                        $agencyName = $agency->getName();
                        $agencyCode = $agency->getCode();
                        $agencyId = $agency->getId();
                    }
                }
            }
        }

        $order->setAgencyName($agencyName)
            ->setAgencyCode($agencyCode)
            ->setAgencyId($agencyId)
        ;

        $fields = $bf->getFieldPolicy($companyName);
        $mm = $bf->getMetadataManager($companyName);
        list($metadatas, $append_metadata) = $this->getMetadatas(true, false, $companyName);
        $pictures = $mm->buildValue($posts, $metadatas);

        //根据策略判断是否需要视频模块
        if($fields['video']) {
            if (isset($posts["append_video"])) {
                $videos = $order->getVideos();
            } else {
                $videoMetadatas = $this->getVideoMetaDatas(false, false, $companyName);
                $videos = $mm->buildValue($posts, $videoMetadatas); 
            } 
        } else {
            $videos = [];
        }

        // 处理append逻辑
        if (isset($posts["append"]) && !empty($posts['append'])) {
            $append_key = $this->findAppendKey($order);
            $append_value = $mm->buildValue($posts, [$append_metadata]);
            $append_value["append_$append_key"] = $append_value["append"];
            unset($append_value["append"]);
            $pictures = array_merge($pictures, $append_value);
        }

        // 处理appendVideo逻辑(有append_video 追加，无 新建)
        if (isset($posts["append_video"]) && !empty($posts["append_video"])) {
            if(isset($videos['append_video'])) {
                array_push($videos['append_video'], $posts["append_video"][0]);
            } else {
                $videos['append_video'] = $posts["append_video"];
            }
        }

        $order->setPictures($pictures);
        $order->setVideos($videos);
        if ($submit) {
            $order->setStatus(Order::STATUS_EXAM);
            $order->setSubmitedAt(new \DateTime());
        }

        // 如果第一次创建的新单子是海通恒运，默认给10次复制机会
        if ($createNew == true) {
            if (!$order->getParent() && $order->getCompany()->getCompany() == '海通恒运') {
                $order->setAllowCopyTimes(10);
            }
        }

        $em->flush();

        if($submit) {
            //添加订单提交事件
            $this->addOrderSubmitEvent($order);
        }

    }

    //获取$mm 公司对应的metadatamanager
    public function getMetadataManager($company = null)
    {
        if(!$company){
            $company = $this->getUser()? $this->getUser()->getAgencyRels()[0]->getCompany()->getCompany() : null;
        }
        $bf = $this->get('app.business_factory');

        return $bf->getMetadataManager($company);
    }


    public function backReasonMetadataForApp($company = null)
    {
        $mm = $this->getMetadataManager($company);
        $metadatas = $mm->getMetadata4BackReason(false);
        $ret = [];
        foreach ($metadatas as  $k=>$metadata) {
                $ret[$k]['key'] = $metadata->key;
                $ret[$k]['display'] = $metadata->display;
                $ret[$k]['sample'] = $metadata->options['sample'];
        }
        return $ret;
    }

    public function getPictureMetadatas($company = null)
    {
        $order = $this->getMetadatas(false, false, $company);
        $getPictureRequire = $this->getPictureRequireMetadata($company);
        $ret = [];
        $group = $order['0']->options;
        $groups = $group['groups'];
        foreach($order as $k=>$value){
            $ret[$k]['group'] = $this->getAppGroup($value, $groups);
            $ret[$k]['key'] = $value->key;
            $ret[$k]['type'] = $value->type;
            $ret[$k]['display'] = $value->display;
            $options = $value->options;
            $ret[$k]['sub_groups'] = isset($options['subGroups'])?$options['subGroups']:[];
            $ret[$k]['least'] = isset($options['least'])?$options['least']:"";
            $ret[$k]['most'] = isset($options['most'])?$options['most']:"";
            $tips = $getPictureRequire[$value->key]['tips'];

            $ret[$k]['sample'] = $tips ? $this->getParameter('qiniu_domain')."/img_sample2/".$tips : '';
            $mask = $getPictureRequire[$value->key]['mask'];
            $ret[$k]['mask'] = $mask ? "http://asset.youyiche.com/hpl_app/img_mask/".$mask : '';
            $ret[$k]['todo'] = $getPictureRequire[$value->key]['todo'];
            $ret[$k]['require'] = $getPictureRequire[$value->key]['require'];
        }
        return $ret;
    }

    public function getPictureRequireMetadata($company = null)
    {
        $mm = $this->getMetadataManager($company);
        $pictureRequire = $mm->pictureRequireMetadata();
        return $pictureRequire;
    }

    public function getAppGroup($value,&$groups)
    {
        $op = $value->options;
        $groups = isset($op['groups'])?$op['groups']:$groups;
        return $groups;
    }

    public function getAppendMetadata($company = null)
    {
        list($metadatasall, $append_metadata) = $this->getMetadatas(true, false, $company);
        $options = $append_metadata->options;
        $vars['key'] = $append_metadata->key;
        $vars['type'] = $append_metadata->type;
        $vars['display'] = $append_metadata->display;
        $vars['group'] = $options['groups'];
        $vars['least'] = isset($options['least'])?$options['least']:"";
        $vars['most'] = isset($options['most'])?$options['most']:"";
        return $vars;
    }

    public function getPictureGroups()
    {
        $mm = $this->getMetadataManager();
        $pictureGroups = $mm->getGroupsForApp();
        return $pictureGroups;
    }

    //新增视频重新构建groups
    public function getNewPictureGroups()
    {
        $mm = $this->getMetadataManager();
        $pictureGroups = $mm->getNewGroupsForApp();
        return $pictureGroups;
    }


    // 废弃
    public function getTitleMetadataForApp($company = null)
    {
        $mm = $this->getMetadataManager($company);
        $titleMetadata = $mm->getTitleMetadataForApp();
        $bf = $this->get('app.business_factory');
        $fields = $bf->getFieldPolicy($company);

        $titleMetadata[3]['show'] = $fields['valuation'];      //估价
        $titleMetadata[4]['show'] = $fields['businessNumber']; //编号
        $titleMetadata[5]['show'] = $fields['remark'];          //备注
        $titleMetadata[6]['show'] = $fields['useraskQuertion']; //车况自述
        return $titleMetadata;
    }

    // 要废弃
    public function getAskQuertionMetadata()
    {
        $mm = $this->getMetadataManager();
        return $mm->askQuertionMetadata();
    }

    public function backReasonKeyMetadata()
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $metadatas = $mm->getMetadata4BackReason();
        $ret = [];
        foreach ($metadatas as  $k=>$metadata) {
                $ret[$metadata->key]['key'] = $metadata->key;
                $ret[$metadata->key]['display'] = $metadata->display;
                $ret[$metadata->key]['sample'] = $metadata->options['sample'];
        }
        return $ret;
    }

    //视频退回原因的meta
    public function backReasonVideoKeyMetadata($company = null)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager($company);
        $metadatas = $mm->getMetadata4BackReasonVideo();
        $ret = [];
        foreach ($metadatas as  $k=>$metadata) {
                $ret[$metadata->key]['key'] = $metadata->key;
                $ret[$metadata->key]['display'] = $metadata->display;
                $ret[$metadata->key]['sample'] = $metadata->options['sample'];
        }
        return $ret;
    }

    //后台退回原因的meta
    public function backstageReasonKeyMetadata($company = null)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager($company);
        $metadatas = $mm->getMetadata4BackstageReason();
        $ret = [];
        foreach ($metadatas as  $k=>$metadata) {
                $ret[$metadata->key]['key'] = $metadata->key;
                $ret[$metadata->key]['display'] = $metadata->display;
        }
        return $ret;
    }

    public function getMainPicture($pictures)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $k = $mm->getMainPictureKey();
        return isset($pictures[$k]) && count($pictures[$k]) !=0 ? $pictures[$k][0] : '';
    }

    public function getPicturesKeyMetaDatas($company = null)
    {
        list($metadatas, $append_metadata) = $this->getMetadatas(true, false, $company);
        $pictures = [];
        foreach($metadatas as $metadata){
            $pictures[$metadata->key]['key'] = $metadata->key;
            $pictures[$metadata->key]['display'] = $metadata->display;
            $pictures[$metadata->key]['group'] = $metadata->options['groups'];
        }
        return $pictures;
    }

    public function getMetadatas($withAppend = false, $getGroups = false, $company = null)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager($company);
        $metadatas = $mm->getMetadata4Order();
        $groups = $mm->getGroups();
        $index = -1;
        foreach ($metadatas as $key => $value) {
            if ($value->key === "append") {
                $index = $key;
                break;
            }
        }
        $append_metadata = array_splice($metadatas, $index, 1);

        if ($withAppend) {
            if ($getGroups) {
                $groups = $mm->getGroups();

                return [$metadatas, $append_metadata[0], $groups];
            } else {
                return [$metadatas, $append_metadata[0]];
            }
        } elseif ($getGroups) {
            return [$metadatas, $groups];
        }

        return $metadatas;
    }

    public function getVideoMetadatas($withAppend = false, $getVideoGroups = false, $company = null)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager($company);
        $metadatas = $mm->getMetadata4OrderVideo();
        $groups = $mm->getVideoGroups();
        $index = -1;
        foreach ($metadatas as $key => $value) {
            if ($value->key === "append_video") {
                $index = $key;
                break;
            }
        }
        $append_metadata = array_splice($metadatas, $index, 1);

        if ($withAppend) {
            if ($getVideoGroups) {
                $groups = $mm->getVideoGroups();

                return [$metadatas, $append_metadata[0], $groups];
            } else {
                return [$metadatas, $append_metadata[0]];
            }
        } elseif ($getVideoGroups) {
            return [$metadatas, $groups];
        }

        return $metadatas;
    }

    /**
     *获取视频meta字段
    */
    public function getVideosKeyMetaDatas($company = null)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager($company);
        $metadatas = $mm->getMetadata4OrderVideo();
        $videos = [];
        foreach($metadatas as $metadata){
            $videos[$metadata->key]['key'] = $metadata->key;
            $videos[$metadata->key]['display'] = $metadata->display;
            $videos[$metadata->key]['group'] = $metadata->options['groups'];
        }
        return $videos;
    }

    /**
     * 获取订单额额外的meta字段
     */
    public function getOrderExtraMetadatas($company = null)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager($company);
        $metadatas = $mm->getMetadata4OrderExtra();

        return $metadatas;
    }

    public function findAppendKey($order)
    {
        $back_count = count($order->getBacks());
        return $back_count + 1;
    }

    public function matchBackReasonMetas($reasons){
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $metadatas = $mm->getMetadata4BackReason();
        $ret = [];
        foreach ($metadatas as $metadata) {
            if (isset($reasons[$metadata->key]) && $reasons[$metadata->key]["value"] != "正常") {
                $metadata->value = $reasons[$metadata->key];
                $ret[] = $metadata;
            }
        }
        return $ret;
    }

    //匹配视频退回原因
    public function matchBackReasonVideoMetas($reasons)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $metadatas = $mm->getMetadata4BackReasonVideo();
        $ret = [];
        foreach ($metadatas as $metadata) {
            if (isset($reasons[$metadata->key]) && $reasons[$metadata->key]["value"] != "正常") {
                $metadata->value = $reasons[$metadata->key];
                $ret[] = $metadata;
            }
        }
        return $ret;
    }

    //匹配后台退回原因
    public function matchBackstageReasonMetas($reasons)
    {
        $bf = $this->get('app.business_factory');
        $mm = $bf->getMetadataManager();
        $metadatas = $mm->getMetadata4BackstageReason();
        $ret = [];
        if(!isset($reasons['reason_v1'])) {
            $reasons['reason_v1']['value'] = null; 
        }
        foreach ($metadatas as $metadata) {
            if (isset($reasons[$metadata->key])) {
                if($reasons[$metadata->key]["value"] != "正常") {
                    $metadata->value = $reasons[$metadata->key]['value'];
                    $ret[] = $metadata;
                } else {
                    $metadata->value = null;
                    $ret[] = $metadata;
                }
            }
        }
        return $ret;
    }

    /**
     * 建立order与report的关联
     * @param $order
     * @return Report
     */
    public function associateOrderReport($order)
    {
        $report = new Report();
        $report->setExamer($this->getUser())
                ->setReport([])
                ->setSecReport([]);
        $em = $this->getDoctrineManager();
        $em->persist($report);

        $order->setReport($report);

        $em->flush();
        return $report;
    }

    /**
     * 供渲染menu菜单数量时调用各订单状态(草稿箱，已提交，已退回，高价复核)数量
     * 
     */
    public function countOrder()
    {
        $draftCount = 0;
        $submittedCount = 0;
        $backCount = 0;

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $companyName = null;
        } else {
            // 拥有ROLE_EXAMER_HPL角色只能看到和自己公司名称一样的记录
            $companyName = $this->getUser()->getAgencyRels()[0]->getCompany()->getCompany();
        }

        $recheckCount = $this->getDoctrine()->getRepository('AppBundle:Order')->findAllRecheckCount($companyName);
        $count = array('draftCount' => $draftCount, 'submittedCount' => $submittedCount, 'backCount' => $backCount, 'recheckCount' => $recheckCount);

        return $count;
    }

    /**
     * 根据业务的type id和评估单号来获取数据
     * type为1主要是车基本信息，为2车价格信息，为3各图片信息
     * 返回结果是json格式
     */
    public function getHplData($type, $orderNo)
    {
        $orderHpl = $this->getRepo('AppBundle:Order')->findCompanyOrder(Config::COMPANY_HPL, $orderNo);
        $orderCbt = $this->getRepo('AppBundle:Order')->findCompanyOrder(Config::COMPANY_HPL_CBT, $orderNo);

        $order = $orderHpl ? $orderHpl : $orderCbt;
        if ($order) {
            $report = $order->getReport();
            if ('1' === $type) {
                return $this->getHplCarBasicInfo($order);
            } elseif ('2' === $type) {
                return $this->getHplCarPriceInfo($order);
            } elseif ('3' === $type) {
                return $this->getHplCarImgUrl($order);
            }
        } else {
            return;
        }
    }

    /**
     * 根据业务的type id和评估单号来获取数据
     * type为1主要是车基本信息，为2车价格信息，为3各图片信息
     * 返回结果是json格式
     */
    public function getPinganData($type, $orderNo)
    {
        $order = $this->getRepo('AppBundle:Order')->findCompanyOrder(Config::COMPANY_PINGAN, $orderNo);

        if ($order) {
            $report = $order->getReport();
            if ('1' === $type) {
                return $this->getCarBasicInfo($order);
            } elseif ('2' === $type) {
                return $this->getCarPriceInfo($order);
            } elseif ('3' === $type) {
                return $this->getCarImgUrl($order);
            }
        } else {
            return;
        }
    }


    public function getHplCarBasicInfo($order)
    {
        // $carInfo主要为车辆的基本信息
        $report = $order->getReport();

        $carInfo['pgdh'] = $order->getOrderNo();//评估单号
        $carInfo['khdm'] = $order->getAgencyCode();//供应商代码(简称) 
        $carInfo['khmc'] = $order->getAgencyName();//供应商名称
        $carInfo['czdm'] = '';//车主代码
        $carInfo['czmc'] = '';//车主名称
        $carInfo['xsz'] = '';//行驶证
        $carInfo['hgz'] = '';//合格证
        $carInfo['cxdm'] = $report->getReport()['field_1030']['value']; //车型代码(厂牌型号)?
        $carInfo['cjhm'] = $report->getVin();              //车架号
        $carInfo['fdjh'] = $report->getReport()['field_1050']['value']; //发动机号
        $carInfo['cphm'] = $report->getReport()['field_1010']['value']; //车牌号码
        $carInfo['cllx'] = $report->getReport()['field_3060']['value']; //车辆类型
        $carInfo['gl'] = $report->getReport()['field_3080']['value']; //功率
        $carInfo['pl'] = $report->getReport()['field_3020']['value']; //排量
        $carInfo['hbbz'] = $report->getReport()['field_3090']['value']; //环保标准
        $carInfo['syxz'] = $report->getReport()['field_1020']['value']; //使用性质
        $carInfo['pp'] = $report->getReport()['field_2010']['value']; //品牌
        $carInfo['px'] = $report->getReport()['field_2020']['value']; //车系
        $carInfo['cx'] = $report->getReport()['field_2030']['value']; //车型
        $carInfo['bb'] = ''; //版本
        $carInfo['gcrq'] = ''; //购车日期

        $ccrq = $report->getReport()['field_3040']['value']; //出厂日期
        // 如果获取到的出厂日期没有精确到日，自动精确到1号
        if (strlen($ccrq) <= 7 && strlen($ccrq) > 0) {
            $carInfo['ccrq'] = $ccrq.'/01';
        } else {
            $carInfo['ccrq'] = $ccrq;
        }

        $carInfo['djrq'] = $report->getReport()['field_1060']['value']; //登记日期
        $carInfo['nsyxq'] = $report->getReport()['field_1070']['value']; //年审有效期
        $carInfo['xslc'] = $report->getReport()['field_3010']['value'] ; //行驶里程
        $carInfo['gcjg'] = '';                                                  //购车价格
        $carInfo['zl'] = '';                                                    //质量
        $carInfo['zz'] = '';                                                  //载重
        $carInfo['zw'] = $report->getReport()['field_3050']['value']; //座位
        $carInfo['bsxs'] = $report->getReport()['field_3100']['value']; //变速形式
        $carInfo['cmxs'] = $report->getReport()['field_3110']['value']; //车门型式
        $carInfo['cdfs'] = $report->getReport()['field_3120']['value']; //传动方式 
        $carInfo['gyxt'] = $report->getReport()['field_3070']['value']; //供油系统
        $carInfo['jrfs'] = $report->getReport()['field_3130']['value']; //进气方式
        $carInfo['pzqt'] = '';                                                  //配置其他
        $carInfo['tspbsm'] = '';            //特殊配置说明
        $carInfo['tscgywsm'] = '';                                             //特使车管业务说明
        $carInfo['zdr'] = $order->getLoadOfficer()->getName();                                                  //制单人
        $carInfo['zdrq'] = $order->getCreatedAt()->format('Y-m-d H:i:s');    //制单日期
        $carInfo['bz'] = $order->getRemark();                                                   //备注(采集员备注处)
        $carInfo['zt'] = '';                                                   //状态 ?
        $carInfo['tjrq'] = $order->getSubmitedAt()->format('Y-m-d H:i:s');     //提交日期 
        $carInfo['pgsdm'] = $report->getExamer()->getId();        //评估师代码
        $carInfo['pgrq' ] = $report->getCreatedAt() ? $report->getCreatedAt()->format('Y-m-d H:i:s') : '';  //评估日期
        $carInfo['cyhsm'] = '';                                                //差异化说明
        $carInfo['ys'] = $report->getReport()['field_3030']['value']; //颜色
        $carInfo['ishsc'] = '';                                                //是否火烧车
        $carInfo['ispsc'] = '';                                                //是否泡水车
        $carInfo['wjsl' ] = '';                                                 //文件数量
        $carInfo['tbrsj'] = '';                                                //采集员手机
        $carInfo['clsyxz'] = '';                                         //使用性质
        $carInfo['escysj]g'] = $report->getReport()['field_4012']['value']; //二手车预售价格
        $carInfo['zdrmc'] = $order->getLoadOfficer()->getName();//采集员名称
        $carInfo['dwdm' ] = '';                    //采集员所在单位的单位简称
        $carInfo['dwmc' ] = '';                    //采集员所在单位名称
        $carInfo['ckpg' ] = $report->getReport()['field_4150']['value']; //车况评估
        $carInfo['shrq' ] = $report->getExamedAt() ? $report->getExamedAt()->format('Y-m-d H:i:s') : ''; //审核日期
        $carInfo['success'] = true;

        return $carInfo;
    }

    public function getCarBasicInfo($order)
    {
        // $carInfo主要为车辆的基本信息
        $report = $order->getReport();

        $carInfo['passed'] = $report->getStatus() === Report::STATUS_REFUSE ? false : true;//是否又一车评估通过

        if ('拒绝放贷' === $report->getReport()['field_result']['value']) {
            $carInfo['refusedReason'] = $report->getReport()['field_result']['options']['textarea'];
        } else {
            $carInfo['refusedReason'] = '';
        }

        $carInfo['pgdh'] = $order->getOrderNo();//评估单号
        $carInfo['khdm'] = $order->getAgencyCode();//供应商代码(简称) 
        $carInfo['khmc'] = $order->getAgencyName();//供应商名称
        $carInfo['czdm'] = '';//车主代码
        $carInfo['czmc'] = '';//车主名称
        $carInfo['xsz'] = '';//行驶证
        $carInfo['hgz'] = '';//合格证
        $carInfo['cxdm'] = $report->getReport()['field_1030']['value']; //车型代码(厂牌型号)?
        $carInfo['cjhm'] = $report->getVin();              //车架号
        $carInfo['fdjh'] = $report->getReport()['field_1050']['value']; //发动机号
        $carInfo['cphm'] = $report->getReport()['field_1010']['value']; //车牌号码
        $carInfo['cllx'] = $report->getReport()['field_3060']['value']; //车辆类型
        $carInfo['gl'] = $report->getReport()['field_3080']['value']; //功率
        $carInfo['pl'] = $report->getReport()['field_3020']['value']; //排量
        $carInfo['hbbz'] = $report->getReport()['field_3090']['value']; //环保标准
        $carInfo['syxz'] = $report->getReport()['field_1020']['value']; //使用性质
        $carInfo['pp'] = $report->getReport()['field_2010']['value']; //品牌
        $carInfo['px'] = $report->getReport()['field_2020']['value']; //车系
        $carInfo['cx'] = $report->getReport()['field_2030']['value']; //车型

        $carInfo['bb'] = ''; //版本
        $carInfo['gcrq'] = ''; //购车日期

        $ccrq = $report->getReport()['field_3040']['value']; //出厂日期
        // 如果获取到的出厂日期没有精确到日，自动精确到1号
        if (strlen($ccrq) <= 7 && strlen($ccrq) > 0) {
            $carInfo['ccrq'] = $ccrq.'/01';
        } else {
            $carInfo['ccrq'] = $ccrq;
        }

        $carInfo['djrq'] = $report->getReport()['field_1060']['value']; //登记日期
        $carInfo['nsyxq'] = $report->getReport()['field_1070']['value']; //年审有效期
        $carInfo['xslc'] = $report->getReport()['field_3010']['value'] ; //行驶里程
        $carInfo['gcjg'] = '';                                                  //购车价格
        $carInfo['zl'] = '';                                                    //质量
        $carInfo['zz'] = '';                                                  //载重
        $carInfo['zw'] = $report->getReport()['field_3050']['value']; //座位
        $carInfo['bsxs'] = $report->getReport()['field_3100']['value']; //变速形式
        $carInfo['cmxs'] = $report->getReport()['field_3110']['value']; //车门型式
        $carInfo['cdfs'] = $report->getReport()['field_3120']['value']; //传动方式 
        $carInfo['gyxt'] = $report->getReport()['field_3070']['value']; //供油系统
        $carInfo['jrfs'] = $report->getReport()['field_3130']['value']; //进气方式
        $carInfo['pzqt'] = '';                                                  //配置其他
        $carInfo['tspbsm'] = '';            //特殊配置说明
        $carInfo['tscgywsm'] = '';                                             //特使车管业务说明
        $carInfo['zdr'] = $order->getLoadOfficer()->getName();                                                  //制单人
        $carInfo['zdrq'] = $order->getCreatedAt()->format('Y-m-d H:i:s');    //制单日期
        $carInfo['bz'] = $order->getRemark();                                                   //备注(采集员备注处)
        $carInfo['zt'] = '';                                                   //状态 ?
        $carInfo['tjrq'] = $order->getSubmitedAt()->format('Y-m-d H:i:s');     //提交日期 
        $carInfo['pgsdm'] = $report->getExamer()->getId();        //评估师代码
        $carInfo['pgrq' ] = $report->getCreatedAt() ? $report->getCreatedAt()->format('Y-m-d H:i:s') : '';  //评估日期
        $carInfo['cyhsm'] = '';                                                //差异化说明
        $carInfo['ys'] = $report->getReport()['field_3030']['value']; //颜色

        if ($report->getReport()['field_4140']['value']) {
            $carInfo['ishsc'] = in_array("火烧车", @$report->getReport()['field_4140']['value']) ? true : false; //是否火烧车
            $carInfo['ispsc'] = in_array("泡水车", @$report->getReport()['field_4140']['value']) ? true : false; //是否泡水车
            $carInfo['issgc'] = in_array("事故车", @$report->getReport()['field_4140']['value']) ? true : false; //是否事故车
        } else {
            $carInfo['ishsc'] = false;                                                                //是否火烧车
            $carInfo['ispsc'] = false;                                                                //是否泡水车
            $carInfo['issgc'] = false;                                                                 //是否事故车
        }

        $carInfo['wjsl' ] = '';                                                 //文件数量
        $carInfo['tbrsj'] = '';                                                //采集员手机
        $carInfo['clsyxz'] = '';                                         //使用性质
        $carInfo['escysjg'] = $order->getValuation(); //二手车预售价格
        $carInfo['zdrmc'] = $order->getLoadOfficer()->getName();//采集员名称
        $carInfo['dwdm' ] = '';                    //采集员所在单位的单位简称
        $carInfo['dwmc' ] = '';                    //采集员所在单位名称
        $carInfo['ckpg' ] = $report->getReport()['field_4150']['value']; //车况评估
        $carInfo['shrq' ] = $report->getExamedAt() ? $report->getExamedAt()->format('Y-m-d H:i:s') : ''; //审核日期
        $carInfo['ghcs'] = $report->getReport()['field_1080']['value']; //过户次数;
        $carInfo['nk'] = $report->getReport()['field_2040']['value']; //年款;

        $province = $order->getLoadOfficer()->getProvince()->getName();
        if ('黑龙江' === $province or '内蒙古' === $province) {
            $carInfo['sf'] = $province;
        } else {
            // 截取省份前2位，
            $carInfo['sf'] = mb_substr($province, 0, 2, 'utf-8');
        }
        $carInfo['city'] = $order->getLoadOfficer()->getCity()->getName(); //城市;


        // 查询车型库映射关系
        $carMap = $this->getRepo('AppBundle:CarMap')->findOneBy(array('brand' => $carInfo['pp'], 'series' => $carInfo['px'], 'model' => $carInfo['cx'], 'year' => $carInfo['nk']));

        if ($carMap) {
            $carInfo['fypp'] = $carMap->getTransBrand();
            $carInfo['fypx'] = $carMap->getTransSeries();
            $carInfo['fycx'] = $carMap->getTransModel();
            $carInfo['fynk'] = $carMap->getTransYear();
            $carInfo['cs'] = $carMap->getManufacturer();
        } else {
            $carInfo['fypp'] = '';
            $carInfo['fypx'] = '';
            $carInfo['fycx'] = '';
            $carInfo['fynk'] = '';
            $carInfo['cs'] = '';
        }

        $carInfo['maintain'] = $this->get('MaintainLogic')->getMaintainData($report);//维保信息
        $carInfo['insurance'] = $this->get('InsuranceLogic')->getInsuranceData($order);//保险信息

        return ['success' => true, 'return' => $carInfo];
    }

    public function getCarPriceInfo($order)
    {
        // $carPrice主要为车辆的价格信息
        $report = $order->getReport();

        $fuelType = $report->getReport()['field_3070']['value'];//燃油类型
        $carType = $report->getReport()['field_3060']['value'];//车辆类型
        $pxjkc = isset($report->getReport()['field_1021']) ? count($report->getReport()['field_1021']['value']) : '';//平行进口车

        if (in_array($fuelType, array('油电混合', '电力', '插电式混动'))) {
            $carPrice['ckpj' ] = '新能源车';
        } elseif ($pxjkc) {
            $carPrice['ckpj' ] = '平行进口车';
        } elseif (in_array($carType, array('轿车', '跑车', '掀背车', '旅行车', 'SUV', 'MPV'))) {
            $carPrice['ckpj' ] = '乘用车';
        } elseif (in_array($carType, array('客车'))) {
            $carPrice['ckpj' ] = '轻客';
        } elseif (in_array($carType, array('皮卡'))) {
            $carPrice['ckpj' ] = '皮卡';
        } elseif (in_array($carType, array('微面'))) {
            $carPrice['ckpj' ] = '微面';
        } elseif (in_array($carType, array('轻卡'))) {
            $carPrice['ckpj' ] = '轻卡';
        } else {
            $carPrice['ckpj' ] = '乘用车';
        }

        $carPrice['pgdh'] = $order->getOrderNo();                                   //评估单号
        $carPrice['xcj'] = $report->getReport()['field_4020']['value']; //新车价
        $carPrice['yhj'] = '';                                               //优惠价 ?
        $carPrice['gzs'] = $report->getReport()['field_4030']['value']; //购置税
        $carPrice['grj'] = $report->getReport()['field_4040']['value']; //购入价
        $carPrice['nfcxl'] = $report->getReport()['field_4050']['value']; //年份成新率
        $carPrice['yflzxs'] = '';                                               //月份调整系数 ?
        $carPrice['sclrxxs'] = $report->getReport()['field_4070']['value']; //市场冷热销系数
        $carPrice['gxhdxs'] = $report->getReport()['field_4080']['value']; //更新换代系数
        $carPrice['clbbxs'] = $report->getReport()['field_4090']['value']; //车辆版本系数
        $carPrice['glxs'] = $report->getReport()['field_4100']['value']; //公里系数
        $carPrice['ckxs'] = $report->getReport()['field_4090']['value']; //车况系数
        $carPrice['pgjg'] = $report->getReport()['field_4012']['value']; //评估价格 ？
        $carPrice['pgyxq'] = '15';                                                //评估有效期
        $carPrice['pgdw'] = '上海麦拉汽车服务有限公司';                            //评估单位
        $carPrice['pgsdm'] = $report->getExamer()->getId();        //评估师代码
        $carPrice['pgsmc'] = $report->getExamer()->getName();      //评估师名称
        $carPrice['pgrq'] = $report->getExamedAt()->format('Y-m-d H:i:s');//评估日期
        $carPrice['cltz'] = $report->getReport()['field_4060']['value']; //车龄调整
        $carPrice['ysxs'] = $report->getReport()['field_4120']['value']; //颜色系数
        $carPrice['zxf'] = $report->getReport()['field_4130']['value']; //整修费
        $carPrice['schqjg'] = '';                                     //市场行情价格
        $carPrice['tjrq'] = $order->getSubmitedAt()->format('Y-m-d H:i:s');//提交日期 ?
        $carPrice['shrq'] = $report->getExamedAt() ? $report->getExamedAt()->format('Y-m-d H:i:s') : '';//审核日期 ?
        $carPrice['shyj'] = '';                                                 //审核意见
        $carPrice['shr'] = '';                                             //审核人
        $carPrice['shrmc'] = '';                                          //审核人名称
        $carPrice['shbz'] = '';                                           //审核标识
        $carPrice['wgpgrq'] = '';                                         //外观评估日期
        $carPrice['wgtjrq'] = '';                                         //外观提交日期
        $carPrice['wgpgsdm'] = '';                                        //外观评估师代码
        $carPrice['wgpgsdm'] = '';                                        //评估意见

        return ['success' => true, 'return' => $carPrice];
    }

    public function getHplCarPriceInfo($order)
    {
        // $carPrice主要为车辆的价格信息
        $report = $order->getReport();

        $fuelType = $report->getReport()['field_3070']['value'];//燃油类型
        $carType = $report->getReport()['field_3060']['value'];//车辆类型
        $pxjkc = isset($report->getReport()['field_1021']) ? count($report->getReport()['field_1021']['value']) : '';//平行进口车

        if (in_array($fuelType, array('油电混合', '电力', '插电式混动'))) {
            $carPrice['ckpj' ] = '新能源车';
        } elseif ($pxjkc) {
            $carPrice['ckpj' ] = '平行进口车';
        } elseif (in_array($carType, array('轿车', '跑车', '掀背车', '旅行车', 'SUV', 'MPV'))) {
            $carPrice['ckpj' ] = '乘用车';
        } elseif (in_array($carType, array('客车'))) {
            $carPrice['ckpj' ] = '轻客';
        } elseif (in_array($carType, array('皮卡'))) {
            $carPrice['ckpj' ] = '皮卡';
        } elseif (in_array($carType, array('微面'))) {
            $carPrice['ckpj' ] = '微面';
        } elseif (in_array($carType, array('轻卡'))) {
            $carPrice['ckpj' ] = '轻卡';
        } else {
            $carPrice['ckpj' ] = '乘用车';
        }

        $carPrice['pgdh'] = $order->getOrderNo();                                   //评估单号
        $carPrice['xcj'] = $report->getReport()['field_4020']['value']; //新车价
        $carPrice['yhj'] = '';                                               //优惠价 ?
        $carPrice['gzs'] = $report->getReport()['field_4030']['value']; //购置税
        $carPrice['grj'] = $report->getReport()['field_4040']['value']; //购入价
        $carPrice['nfcxl'] = $report->getReport()['field_4050']['value']; //年份成新率
        $carPrice['yflzxs'] = '';                                               //月份调整系数 ?
        $carPrice['sclrxxs'] = $report->getReport()['field_4070']['value']; //市场冷热销系数
        $carPrice['gxhdxs'] = $report->getReport()['field_4080']['value']; //更新换代系数
        $carPrice['clbbxs'] = $report->getReport()['field_4090']['value']; //车辆版本系数
        $carPrice['glxs'] = $report->getReport()['field_4100']['value']; //公里系数
        $carPrice['ckxs'] = $report->getReport()['field_4090']['value']; //车况系数
        $carPrice['pgjg'] = $report->getReport()['field_4012']['value']; //评估价格 ？
        $carPrice['pgyxq'] = '15';                                                //评估有效期
        $carPrice['pgdw'] = '上海麦拉汽车服务有限公司';                            //评估单位
        $carPrice['pgsdm'] = $report->getExamer()->getId();        //评估师代码
        $carPrice['pgsmc'] = $report->getExamer()->getName();      //评估师名称
        $carPrice['pgrq'] = $report->getExamedAt()->format('Y-m-d H:i:s');//评估日期
        $carPrice['cltz'] = $report->getReport()['field_4060']['value']; //车龄调整
        $carPrice['ysxs'] = $report->getReport()['field_4120']['value']; //颜色系数
        $carPrice['zxf'] = $report->getReport()['field_4130']['value']; //整修费
        $carPrice['schqjg'] = '';                                     //市场行情价格
        $carPrice['tjrq'] = $order->getSubmitedAt()->format('Y-m-d H:i:s');//提交日期 ?
        $carPrice['shrq'] = $report->getExamedAt() ? $report->getExamedAt()->format('Y-m-d H:i:s') : '';//审核日期 ?
        $carPrice['shyj'] = '';                                                 //审核意见
        $carPrice['shr'] = '';                                             //审核人
        $carPrice['shrmc'] = '';                                          //审核人名称
        $carPrice['shbz'] = '';                                           //审核标识
        $carPrice['wgpgrq'] = '';                                         //外观评估日期
        $carPrice['wgtjrq'] = '';                                         //外观提交日期
        $carPrice['wgpgsdm'] = '';                                        //外观评估师代码
        $carPrice['wgpgsdm'] = '';                                        //评估意见
        $carPrice['success'] = true;

        return $carPrice;
    }

    public function getHplCarImgUrl($order)
    {
        //archive 主要为车辆图片及pdf的url相关信息
        $pictures = $order->getPictures();

        $archive['登记证'] = $pictures['k1'];
        $archive['车身外观'] = [];
        foreach ($pictures as $k => $v) {
            if (in_array($k, ['k1', 'k22'])) {
                continue;
            }

            if (strpos($k, 'append') !== false) {
                $archive['登记证'] = array_merge($archive['登记证'], $v);
                continue;
            }

            $archive['车身外观'] = array_merge($archive['车身外观'], $v);
        }

        $archive['车体骨架1'] = '';
        $archive['车体骨架2'] = '' ;
        $archive['车辆内饰'] = '';
        $archive['铭牌'] = $pictures['k22'];
        $archive['补充照片'] = '';
        $archive['原车保险'] = '';
        $archive['行驶证'] = '';

        $companyName = $order->getCompany()->getCompany();

        $newArchive = array();
        // 构造出可访问的url地址
        foreach ($archive as $key1 => $value1) {
            // 如果有值的构造出可以访问的url
            if ($value1) {
                foreach ($value1 as $key2 => $value2) {
                    $imgUrl = $this->getImgUrl($companyName, $value2);
                    $newArchive[$key1][$key2] = $imgUrl;
                }
            } else {
                $newArchive[$key1] = $value1;
            }
        }
        $newArchive['pdf'] = $this->generateUrl('pdfreport', array('orderid'=> $order->getId(), '_format' => 'pdf'), UrlGeneratorInterface::ABSOLUTE_URL);
        $newArchive['success'] = true;

        return $newArchive;
    }

    public function getCarImgUrl($order)
    {
        //archive 主要为车辆图片及pdf的url相关信息
        $pictures = $order->getPictures();
        $archive['certPhotos'] = [];
        // 登记证，铭牌
        $archive['certPhotos'] = array_merge($pictures['k1'] ?? [], $pictures['k22'] ?? []);
        //外观
        $archive['carPhotos'] = [];

        $archive['otherPhotos'] = []; // 补充照片


        foreach ($pictures as $k => $v) {
            if (in_array($k, ['k1', 'k22'])) {
                continue;
            }

            if (strpos($k, 'append') !== false) {
                $archive['otherPhotos'] = array_merge($archive['otherPhotos'], $v);
                continue;
            }

            $archive['carPhotos'] = array_merge($archive['carPhotos'], $v);
        }

        $companyName = $order->getCompany()->getCompany();

        $newArchive = array();
        // 构造出可访问的url地址
        foreach ($archive as $key1 => $value1) {
            // 如果有值的构造出可以访问的url
            if ($value1) {
                foreach ($value1 as $key2 => $value2) {
                    $imgUrl = $this->getImgUrl($companyName, $value2);
                    $newArchive[$key1][$key2] = $imgUrl;
                }
            } else {
                $newArchive[$key1] = $value1;
            }
        }
        $newArchive['pdf'] = $order->getOrderNo().'.pdf';

        return ['success' => true, 'return' => $newArchive];
    }


    public function getImgUrl($companyName = null, $imgName = null)
    {
        if (Config::COMPANY_PINGAN === $companyName) {
            $domain = $this->getParameter('pinganyun_url');
            $bucket = $this->getParameter('pinganyun_bucket');
            // 截取图片名字中包含的'hpl/'4个字符,因为上传到平安云的图片已去掉前缀
            $imgName = substr($imgName, 4);
            // $url = $domain.'/download/'.$bucket.'/'.$imgName;
            $url = $imgName;
        } else {
            $domain = $this->getParameter('qiniu_domain');
            $url = $domain.'/'.$imgName;
        }

        return $url;
    }

    public function updateOrderAddress(Order $order)
    {
        $em = $this->getDoctrineManager();
        $longitude = $order->getLongitude();
        $latitude = $order->getLatitude();
        if(!$longitude || !$latitude){
            $province = '';
            if ($order->getLoadOfficer()->getProvince()) {
                $province = $order->getLoadOfficer()->getProvince()->getName();
            }
            
            $city = '';
            if ($order->getLoadOfficer()->getCity()) {
                $city = $order->getLoadOfficer()->getCity()->getName();
            }
            $order->setPersonProvince($province);
            $order->setPersonCity($city);
            $order->setCarProvince($province);
            $order->setCarCity($city);
            $em->flush();

            return '';
        }
        //使用 google 的 经纬度地址转换api
        // $url = "http://maps.google.cn/maps/api/geocode/json?latlng={$latitude},{$longitude}&language=CN";
        // 注册谷歌api后，无每天2万的次数限制
        // $url = "https://maps.google.cn/maps/api/geocode/json?latlng={$latitude},{$longitude}&language=CN&key=AIzaSyC2SYvzH3MSiwutWPQ6nUT-5WgHh6_9xdQ";
        // $reJson = $this->get('app.third.notify_company')->httpGet($url);
        // $rearr = json_decode($reJson, true);
        // $result = isset($rearr['results']) ? $rearr['results'] : [];

        // if(empty($result) || count($result) < 1){
        //     return '';
        // }

        // $address = $result[0]['formatted_address'];
        // $province = $result[2]['address_components'][1]['long_name'];
        // $city = $result[2]['address_components'][0]['long_name'];

        //使用 baidu 的经纬度地址转换api
        // $url = "http://api.map.baidu.com/geocoder/v2/?location={$latitude},{$longitude}&output=json&pois=0&ak=H7jj1BAQEIGiEF33tnbSuoLZxEHtgWYW";
        $url = "http://api.map.baidu.com/geocoder/v2/?location={$latitude},{$longitude}&output=json&pois=0&ak=dHbuVkYveaiBhSQSEuLBPYDZmXVZqUSb";
        $reJson = $this->get('app.third.notify_company')->httpGet($url);
        $rearr = json_decode($reJson, true);
        $result = isset($rearr['result']) ? $rearr['result'] : [];

        if(empty($result) || count($result) < 1){
            return '';
        }

        $address = $result['formatted_address'];
        $province = $result['addressComponent']['province'];
        $city = $result['addressComponent']['city'];

        $order->setPersonProvince($province);
        $order->setPersonCity($city);
        $order->setCarProvince($province);
        $order->setCarCity($city);
        $order->setOrderAddress($address);
        $em->flush();

        return $address;
    }

    public function getCompanyMetaVersion($company)
    {
        $mm = $this->getMetadataManager($company);
        $mmVersion = $mm->getVersion();

        //获取 company 对应的 config
        $config = $this->getRepo('AppBundle:Config')->findOneBy(['company' => $company]);
        $configVersion = $config ? $config->getVersion() : 0 ;

        return $mmVersion.$configVersion;
    }

    public function isCloneable($order)
    {
        $allowCopyTimes = $order->getAllowCopyTimes();
        if ($allowCopyTimes > 0 && !$order->getParent()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 订单是否允许被审核
     */
    public function allowAudit($order)
    {
        $report = $order->getReport();
        if (!$order->getParent() || $report) {
            return true;
        }

        $parentOrder = $order->getParent();
        if ($parentOrder && $parentOrder->getStatus() === Order::STATUS_DONE) {
            return true;
        }

        return false;
    }

    /**
     * 如果有父单子，直接复制父单子report数据
     */
    public function copyReport($order)
    {
        if ($order->getParent() && !$order->getReport()) {
            $parentOrder = $order->getParent();
            $parentReport = $parentOrder->getReport();

            $newReport = clone $parentReport;
            //将新报告的状态还原成等待审核状态
            $newReport->setStatus(Report::STATUS_WAIT);
            $newReport->setExamer($this->getUser());
            $newReport->setCreatedAt(null);
            $newReport->setExamedAt(null);
            $newReport->setHplExaming(false);
            $newReport->setLocked(false);

            $newReport->setReport($parentReport->getReport());
            $newReport->setSecReport([]);
            $newReport->setRechecker(null);
            $newReport->setStartAt(null);
            $newReport->setEndAt(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newReport);
            $em->flush();

            $order->setReport($newReport);
            $em->persist($order);
            $em->flush();
        }

        return $order;
    }

    /**
     * 审核页面的图片metadatas 暂先死
     */
    public function getSortedMetadatas($metadatas)
    {
        $groups['证件照'] = ['k1', 'k22'];
        $groups['车型图'] = ['k2', 'k3', 'k4', 'k5', 'k130', 'k6', 'k7', 'k135', 'k8', 'k9', 'k14', 'k15', 'k16', 'k17', 'k18', 'k20'];
        $groups['车况'] = ['k140', 'k19', 'k21', 'k10', 'k11', 'k12', 'k13', 'k131'];
        $groups['附加'] = ['k23'];
        $ret = [];

        foreach (array_merge($groups['证件照'], $groups['车型图'], $groups['车况'], $groups['附加']) as $v) {
            foreach ($metadatas as $metadata) {
                if ($metadata->key === $v) {
                    if (in_array($metadata->key, $groups['证件照'])) {
                        $metadata->options['groups'] = '证件照';
                    }

                    if (in_array($metadata->key, $groups['车型图'])) {
                        $metadata->options['groups'] = '车型图';
                    }

                    if (in_array($metadata->key, $groups['车况'])) {
                        $metadata->options['groups'] = '车况';
                    }

                    if (in_array($metadata->key, $groups['附加'])) {
                        $metadata->options['groups'] = '附加';
                    }

                    $ret[] = $metadata;
                    break;
                }
            }
        }

        return $ret;
    }

    /****************private function ********************/
    /**
     * 订单提交事件
     * @param $order
     */
    private function addOrderSubmitEvent($order)
    {
        $dispatcher = new EventDispatcher();
        $subscriber = $this->get("OrderSubmitSubscriber");
        $event = new OrderSubmitEvent($order);
        $dispatcher->addSubscriber($subscriber);
        $dispatcher->dispatch(HplEvents::ORDER_SUBMIT, $event);
    }

    public function findOrderByCompanyNumber($number, $companyName)
    {
        $company = $this->getRepo('AppBundle:Config')->findOneBy(['company' => $companyName]);

        if ($company) {
            $order = $this->getRepo('AppBundle:Order')->findOneBy(['businessNumber' => $number, 'company' => $company]);

            if ($order) {
                return $order;
            }
        }

        return false;
    }

    public function getLayoutMetadatas($companyId)
    {
        $ret['companyId'] = $companyId;
        $companyName = $this->getRepo('AppBundle:Config')->find($companyId)->getCompany();
        $version = $this->getCompanyMetaVersion($companyName);
        $ret['metadata_version'] = $version;
        $groups = $this->getNewPictureGroups();
        $info = $this->getPictureRequireMetadata($companyName);
        $bf = $this->get('app.business_factory');
        $fields = $bf->getFieldPolicy($companyName);
        $mm = $bf->getMetadataManager($companyName);
        $metadatas = $mm->getMetadata4Order();
        foreach ($metadatas as $metadata) {
            $least = $metadata->options['least'] ?? 0;
            $tmp[$metadata->options['groups']][] = [
                'key' => $metadata->key,
                'least' => $least,
                'isMust' => $least > 0 ? true: false,
                'most' => $metadata->options['most'] ?? 0,
                'display' => $info[$metadata->key]['display'] ?? $metadata->display,
                'todo' => $info[$metadata->key]['todo'] ?? '',
                'mask' => isset($info[$metadata->key]['mask']) ? "http://asset.youyiche.com/hpl_app/img_mask/".$info[$metadata->key]['mask'] : '',
                'sample' => isset($info[$metadata->key]['tips']) ? $this->getParameter('qiniu_domain')."/img_sample2/".$info[$metadata->key]['tips'] : '',
                'require' => $info[$metadata->key]['require'] ?? '',
                'type' => 'image',
                'importable' => $fields['importable'],
            ];
        }

        $metadatasVideo = $mm->getMetadata4OrderVideo();
        if(!empty($metadatasVideo)) {
            foreach ($metadatasVideo as $metadataVideo) {
                //因兼容性需求,图片视频公用一个append
                if( $metadataVideo->key == 'append_video' ) continue;
                $least = $metadataVideo->options['least'] ?? 0;
                $tmp[$metadataVideo->options['groups']][] = [
                    'key' => $metadataVideo->key,
                    'least' => $least,
                    'isMust' => $least > 0 ? true: false,
                    'most' => $metadataVideo->options['most'] ?? 0,
                    'display' => $metadataVideo->display,
                    'todo' => '',
                    'mask' => '',
                    'sample' => $metadataVideo->options['sample'] ? $this->getParameter('qiniu_domain').'/'.$metadataVideo->options['sample'] : '',
                    'require' => '',
                    'type' => 'video',
                ];
            }
            foreach ($groups as $group) {
                $ret['metadata'][] = [
                    'title' => $group,
                    'data' => $tmp[$group]
                ];
            }         
        } else {
            array_splice($groups,2,1);
            foreach ($groups as $group) {
                $ret['metadata'][] = [
                    'title' => $group,
                    'data' => $tmp[$group]
                ];
            } 
        }

        $extraFields = $this->getExtraFields($companyName);
        foreach ($extraFields as $key => $field) {
            if ($key === 'businessNumber') {
                array_unshift($ret['metadata'], $extraFields['businessNumber']);
            } else {
                $ret['metadata'] = array_merge($ret['metadata'], [$field]);
            }
        }

        return $ret;
    }

    public function getExtraFields($companyName)
    {
        $ret = [];
        $fields = $this->get('app.business_factory')->getFieldPolicy($companyName);

        if ($fields['businessNumber'] === true) {
            $ret['businessNumber'] = [
                'title' => '业务流水号',
                'data' => [
                    [
                        'isMust' => true,
                        'display' => '业务流水号',
                        'type'=> 'inputBox',
                        'field_name' => 'businessNumber',
                        'hint' => '流水号',
                    ],
                ]
            ];
        }

        if ($fields['valuation'] === true) {
            $ret['valuation'] = [
                'title' => '预售价格',
                'data' => [
                    [
                        'isMust' => true,
                        'display' => '估价',
                        'type'=> 'inputBox',
                        'field_name' => 'valuation',
                        'hint' => '预售价格(元）',
                    ]
                ],
            ];
        }

        if ($fields['remark'] === true) {
            $ret['remark'] = [
                'title' => '备注',
                'data' => [
                    [
                        'isMust' => false,
                        'display' => '备注',
                        'type'=> 'inputBox',
                        'field_name' => 'remark',
                        'hint' => '',
                    ]
                ],
            ];
        }

        return $ret;
    }

    public function getExtraFieldsValue($order)
    {
        $ret = [];
        $companyName = $order->getCompany()->getCompany();
        $fields = $this->get('app.business_factory')->getFieldPolicy($companyName);

        if ($fields['businessNumber'] === true) {
            $ret['businessNumber'] = $order->getBusinessNumber();
        }

        if ($fields['valuation'] === true) {
            $ret['valuation'] = $order->getValuation();
        }

        if ($fields['remark'] === true) {
            $ret['remark'] = $order->getRemark();
        }

        return $ret;
    }
}
