<?php

namespace AppBundle\Business;

use AppBundle\Entity\Order;
use AppBundle\Event\OrderExamEvent;
use AppBundle\Event\OrderRecheckEvent;
use AppBundle\Traits\ContainerAwareTrait;
use AppBundle\Entity\OrderBack;
use AppBundle\Entity\Report;
use Symfony\Component\EventDispatcher\EventDispatcher;
use AppBundle\Event\HplEvents;
use AppBundle\Event\OrderBackEvent;
use AppBundle\Event\OrderFinishEvent;
use AppBundle\Entity\User;
use AppBundle\Entity\Config;
use AppBundle\Entity\ExamerLog;
use AppBundle\Entity\StageLog;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReportLogic
{
    use ContainerAwareTrait;

    public function matchVin($vin)
    {
        $em_youyiche = $this->getDoctrineManager('youyiche');

        $levelIDs = $this->get('Liyang')->matchVin($vin);
        // $levelIDs = ['CFT0540A0111', 'CFT0540A0112', 'CFT0540A0113', 'CFT0540A0114', 'CFT0540A0115',];
        $vehicleinits = $em_youyiche->getRepository("YouyicheBundle:VehiclesInit")->findBy(["levelId" => $levelIDs]);
        if (count($vehicleinits) == 0) {
            throw new \Exception("车型库里没有找到符合此LevelID的车型", 1);
        }

        $diff = [];
        foreach ($vehicleinits as $init) {
            // 暂存所有字段
            foreach (json_decode($init->getCollocate()) as $key => $value) {
                $diff[$key][] = $value;
            }
        }
        foreach ($diff as $key => $value) {
            // "空"改成"无"之后再做判断，防止compare里有太多项
            array_walk($value, function (&$entry) {
                if (empty($entry)) {
                    $entry = "无";
                }
            });
            // 去掉有重复值的字段
            if (1 == count(array_unique($value))) {
                unset($diff[$key]);
            } else {
                $diff[$key] = array_unique($value);
            }
        }

        return [
            'vehicleinits' => $vehicleinits,
            'diff' => $diff
        ];
    }

    public function matchLevelID($levelID)
    {
        $em_youyiche = $this->getDoctrineManager('youyiche');
        $vehicleinit = $em_youyiche->getRepository("YouyicheBundle:VehiclesInit")->findOneByLevelId($levelID);
        return $vehicleinit;
    }

    public function showDiffItem($collocate, $diff)
    {
        $collocate = json_decode($collocate, 1);
        $ret = [];
        foreach ($collocate as $key => $value) {
            if (isset($diff[$key])) {
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    public function checkReport($report, $data, $stage, $type)
    {
        $report->setExamer($this->getUser())
            ->setReport($data)
            ->setStage($stage)
        ;

        if (StageLog::TYPE_UNLOCK == $type) {
            $report->setLocked(false);
        }

        $this->fillbackReport($report);
        $this->getDoctrineManager()->flush();

        return $report;
    }

    public function confirmReport($report, $data, $stage = null, $type = StageLog::TYPE_UNLOCK)
    {
        $report->setRechecker($this->getUser())
            ->setSecReport($data)
        ;

        if ($stage) {
            $report->setStage($stage);
        }

        if (StageLog::TYPE_UNLOCK == $type) {
            $report->setLocked(false);
        }

        $this->fillbackReport($report);
        $this->getDoctrineManager()->flush();

        return $report;
    }

    /**
     * @param Report $report
     * @return Report
     */
    public function passReport(Report $report)
    {
        $report->setStatus(Report::STATUS_PASS);
        // 用于打点
        $report->setStage(Report::STAGE_FINISH);
        $report->setLocked(false);

        $this->updateExamedAt($report);

        //找到该订单的公司名字,然后根据各公司的情况做对应处理
        $order = $this->getRepo('AppBundle:Report')->findOrder($report->getId());
        $company = $order->getCompany()->getCompany();

        if ($this->needRecheck($report, $company)) {
            // 检查复审环节是否需要通知对方接口
            $this->checkRecheckNotice($company, $report);
            //需要复审的，进入复审状态
            $report->setStatus(Report::STATUS_WAIT);
            $report->setHplExaming(true);
        } else {
            //不需要复审，订单变为成功状态
            $this->handleReport($report);
            // 检查非复审环节是否需要通知对方接口
            $this->checkNotice($company, $report);
        }

        $this->getDoctrineManager()->flush();

        //检查结果推送Jpush
        $this->addOrderFinishEvent($report);

        return $report;
    }

    /**
     * 根据公司判断是否需要通知（复审环节）
     */
    public function checkRecheckNotice($company, $report)
    {
        //查询公司配置表看是否有对应的通知url
        $companyUrl = $this->get('app.third.notify_company')->checkNoticeByCompanyConfig($company);

        switch ($company) {
            case Config::COMPANY_PINGAN:
                if ($companyUrl) {
                    $order = $this->getRepo('AppBundle:Report')->findOrder($report->getId());
                    //产生一条上传图片到平安云的rabbitmq message
                    $this->newPinganImgUploadSender($order->getId());
                }
                break;

            default:
                break;
        }
    }

    /**
     * 根据公司判断是否需要通知（非复审环节）
     */
    public function checkNotice($company, $report)
    {
        //查询公司配置表看是否有对应的通知url
        $companyUrl = $this->get('app.third.notify_company')->checkNoticeByCompanyConfig($company);
        $orderNo = $this->getRepo('AppBundle:Report')->findOrder($report->getId())->getOrderNo();

        switch ($company) {
            case Config::COMPANY_PINGAN:
                break;

            default:
                if ($companyUrl) {
                    $this->get("util.rabbitmq")->sendCompanyNotify($orderNo);
                }
                break;
        }
    }

    public function refuseReport($report)
    {
        $report->setStatus(Report::STATUS_REFUSE);
        // 用于打点
        $report->setStage(Report::STAGE_FINISH);
        $report->setLocked(false);
        $this->updateExamedAt($report);
        $this->flushDoctrineManager();
        $this->handleReport($report);

        $order = $this->getRepo('AppBundle:Report')->findOrder($report->getId());
        $company = $order->getCompany()->getCompany();
        if(!in_array($company, [Config::COMPANY_PINGAN, Config::COMPANY_HPL])) {
            $this->checkNotice($company, $report);
        }

        // 失败的单子也推送给平安
        $this->checkRecheckNotice($company, $report);

        //检查结果推送Jpush
        $this->addOrderFinishEvent($report);

        return $report;
    }

    // erp 远程检测单子通过
    public function pass2ERPReport($report)
    {
        $order = $this->getRepo('AppBundle:Report')->findOrder($report->getId());
        $user = $order->getLoadOfficer();
        $userType = $user->getType();
        if ($userType != User::TYPE_TEMP) {
            return;
        }
        $reportStatus = $report->getStatus();
        if ($reportStatus == Report::STATUS_PASS) {
            return;
        }
        $this->updateExamedAt($report);
        $report->setStatus(Report::STATUS_PASS);
        $this->handleReport($report);
        $this->flushDoctrineManager();

        // 关联到的erp里的cid
        $userData = $user->getData();
        if (empty($userData) || empty($userData["k1"])) {
            return;
        }
        $utilrabbitmq = $this->get("util.rabbitmq");
        $msg['report'] = $report->getReport();
        $msg['pictures'] = $order->getPictures();
        $msg['remark'] = $order->getRemark();
        $msg['client'] = $userData["k1"];
        $msg['client_kefu'] = isset($userData["k2"]) ? (int)$userData["k2"] : 0;
        $utilrabbitmq->send("hplreport_sender", $msg);
    }

    public function handleReport($report)
    {
        $this->fillbackReport($report);
        $order = $this->getRepo('AppBundle:Report')->findOrder($report->getId());
        $order->setStatus(Order::STATUS_DONE);
        $this->flushDoctrineManager();
    }

    public function fillbackReport($report)
    {
        $reportData = $report->getReport();
        $report->setVin($reportData['field_1040']['value'])
            ->setBrand($reportData['field_2010']['value'])
            ->setSeries($reportData['field_2020']['value'])
            ->setModel($reportData['field_2030']['value'])
            ->setYear($reportData['field_2040']['value'])
        ;

        if ($reportData['field_2011']['value']) {
            $report->setBrandId($reportData['field_2011']['value']);
        }

        if ($reportData['field_2021']['value']) {
            $report->setSeriesId($reportData['field_2021']['value']);
        }

        if ($reportData['field_2031']['value']) {
            $report->setModelId($reportData['field_2031']['value']);
        }

        if ($reportData['field_2041']['value']) {
            $report->setBiddingCount($reportData['field_2041']['value']);
        }

        if ($reportData['field_2051']['value']) {
            $report->setAveragePrice($reportData['field_2051']['value']);
        }

        if ($reportData['field_4010']['value']) {
            $report->setPurchasePrice($reportData['field_4010']['value']);
        }

        if ($reportData['field_4012']['value']) {
            $report->setSellPrice($reportData['field_4012']['value']);
        }

        if ($reportData['field_4020']['value']) {
            $report->setGuidePrice($reportData['field_4020']['value']);
        }

        // 未来价格只有美车堂有
        if (isset($reportData['field_4014']) && $reportData['field_4014']['value']) {
            $report->setFuturePrice($reportData['field_4014']['value']);
        }

        if ($reportData['field_3010']['value']) {
            $report->setKilometer($reportData['field_3010']['value']);
        }

        if ($reportData['field_1060']['value']) {
            $report->setRegisterDate($reportData['field_1060']['value']);
        }

        return $report;
    }

    public function updateExamedAt($report)
    {
        $order = $this->getRepo('AppBundle:Report')->findOrder($report->getId());
        $report->setExamedAt(new \DateTime());

        if($report->getRechecker()) {
            $report->setEndAt(new \DateTime());
        }
        
        return $report;
    }

    public function createOrderBack($report, $data, $mainReason)
    {
        $order = $this->getRepo("AppBundle:Report")->findOrder($report->getId());
        $orderBack = new OrderBack();
        $orderBack->setExamOrder($order)
            ->setReason($data)
            ->setMainReason($mainReason)
            ->setExamerId($this->getUser()->getId())
            ->setOrgSubmittedAt($order->getSubmitedAt())
        ;
        $order->setLastBack($orderBack);
        $order->setStatus(Order::STATUS_EDIT);
        // 当审核师退回时，会清掉hpl高价复核退回的原因字段，防止任务列表的状态显示有重叠
        $report->setHplReason(null);
        $this->persistAndFlushDoctrineManager($orderBack);

        $dispatcher = new EventDispatcher();
        $subscriber = $this->get("OrderBackSubscriber");
        $event = new OrderBackEvent($report);
        $dispatcher->addSubscriber($subscriber);
        $dispatcher->dispatch(HplEvents::ORDER_BACK, $event);

        return $orderBack;
    }

    public function backfillDataFromInit($prototype)
    {
        $filldata = [
            'field_3020' => ['value' => @$prototype['排量']],
            'field_3050' => ['value' => @$prototype['座位数']],
            'field_3060' => ['value' => @$prototype['车辆类型']],
            'field_3070' => ['value' => @$prototype['燃料类型']],
            'field_3080' => ['value' => @$prototype['最大功率(kW)']],
            'field_3090' => ['value' => @$prototype['环保标准']],
            'field_3110' => ['value' => @$prototype['车门数']],
            'field_3120' => ['value' => @$prototype['驱动形式']],
            'field_3130' => ['value' => @$prototype['进气形式']],
            'field_3140' => ['value' => @$prototype['全景天窗;'] == '有' ? '全景' : '无'],
            'field_3150' => ['value' => [
                @$prototype['真皮座椅;'] == '有' ? '真皮' : null,
                @$prototype['驾驶座座椅电动调节;'] == '有' ? '主驾电动' : null,
                @$prototype['副驾驶座座椅电动调节'] == '有' ? '副驾电动' : null,
                @$prototype['后排座椅电动调节;'] == '有' ? '后排电动' : null,
                @$prototype['前排座椅加热;'] == '有' ? '前排加热' : null,
                @$prototype['后排座椅加热;'] == '有' ? '后排加热' : null,
                @$prototype['电动座椅记忆;'] == '有' ? '记忆' : null,
                @$prototype['座椅通风;'] == '有' ? '通风' : null,
                @$prototype['座椅按摩;'] == '有' ? '按摩' : null,
            ]
            ],
            'field_3155' => ['value' => [
                @$prototype['自动空调;'] == '有' ? '自动' : null,
                @$prototype['后排独立空调;'] == '有' ? '后排独立空调' : null,
            ]
            ],
            'field_3160' => ['value' => @$prototype['后排液晶屏;']],
            'field_3170' => ['value' => @$prototype['定速巡航;'] == '有' || @$prototype['自适应巡航;'] == '有' ? '有' : '无'],
            'field_3180' => ['value' => @$prototype['空气悬挂;']],
            'field_3200' => ['value' => @$prototype['自动头灯;']],
            'field_3210' => ['value' => @$prototype['感应雨刷;']],
            'field_3220' => ['value' => @$prototype['无钥匙启动系统;'] == '有' ? '无钥匙启动' : null],
        ];

        return $filldata;
    }

    /**
     * 更新高价复核数据
     */
    public function updateRecheck($report, $backReason = "")
    {
        $order = $this->getRepo("AppBundle:Report")->findOrder($report->getId());
        $company = $order->getCompany()->getCompany();

        switch ($company) {
            case Config::COMPANY_PINGAN:
                if ($backReason) {
                    $status = Report::STATUS_REFUSE;
                } else {
                    $status = Report::STATUS_PASS;
                }

                $hplExaming = true;
                $this->handleReport($report);//更新order的状态为done
                $this->checkNotice($company, $report);
                break;

            default:
                if ($backReason) {
                    $status = Report::STATUS_WAIT;
                    $hplExaming = false;
                } else {
                    $status = Report::STATUS_PASS;
                    $hplExaming = true;
                    $this->handleReport($report);//更新order的状态为done
                    $this->checkNotice($company, $report);
                }
                break;
        }

        $report->setStatus($status)
            ->setHplExaming($hplExaming)
            ->setHplReason($backReason)
            ->setLocked(false);
            ;

        //高价车复审结果推送Jpush
        $this->addOrderFinishEvent($report);

        $this->getDoctrineManager()->flush();
    }

    /**
     * 记录审核师开始审核的log
     */
    public function handleExamerStartLog($order, $report)
    {
        $em = $this->getDoctrineManager();

        $backOrderTimes = count($this->getDoctrine()->getRepository('AppBundle:OrderBack')->findByExamOrder($order->getId()));

        $examerLog = $this->getDoctrine()->getRepository('AppBundle:ExamerLog')->findOneBy(array('examerId' => $this->getUser()->getId(), 'reportId' => $report->getId(), 'backTimes' => $backOrderTimes));

        if (!$examerLog) {
            $examerLog = new ExamerLog();
            $examerLog->setReportId($report->getId());
            $examerLog->setExamerId($this->getUser()->getId());
            $examerLog->setStartedAt(new \DateTime());
            $examerLog->setBackTimes($backOrderTimes);
            $em->persist($examerLog);
            $em->flush();
        }
    }

    /**
     * 记录审核师保存时的log
     */
    public function handleExamerSaveLog($report)
    {
        $order = $this->getRepo('AppBundle:Report')->findOrder($report->getId());

        $em = $this->getDoctrineManager();

        $backOrderTimes = count($this->getDoctrine()->getRepository('AppBundle:OrderBack')->findByExamOrder($order->getId()));

        $examerLog = $this->getDoctrine()->getRepository('AppBundle:ExamerLog')->findOneBy(array('examerId' => $this->getUser()->getId(), 'reportId' => $report->getId(), 'backTimes' => $backOrderTimes));

        if ($examerLog) {
            $examerLog->setSavedAt(new \DateTime());
            $em->persist($examerLog);
            $em->flush();
        }
    }

    /**
     * 记录审核师不同阶段操作log
     */
    public function handleExamerStageLog($report, $type)
    {
        $stageLog = new StageLog();
        $stageLog->setReportId($report->getId());
        $stageLog->setExamerId($this->getUser()->getId());
        $stageLog->setStage($report->getStage());
        $stageLog->setType($type);

        $em = $this->getDoctrineManager();
        $em->persist($stageLog);
        $em->flush();
    }


    /**
     * 当是平安的订单审核通过时会产生一条上传图片到平安云的rabbitmq message
     */
    public function newPinganImgUploadSender($orderId)
    {
        $mq = $this->get("util.rabbitmq");
        $mq->sendPianganImgUploadNotify($orderId);
    }

    /**
     * 处理平安图片的相关逻辑
     */
    public function handlePinganPicture($orderId)
    {
        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->find($orderId);
        $tempDir = $this->getParameter('temp_save_file_path');
        // 图片名字含有'hpl/'前缀
        $hplDir = $tempDir . 'hpl/';

        if (!file_exists($hplDir)) {
            mkdir($hplDir, 0777, true);
        }

        $qiniuDomain = $this->getParameter('qiniu_domain');

        // 下载
        $handles = [];
        $pictures = $order->getPictures();
        foreach ($pictures as $picture) {
            foreach ($picture as $key) {
                $src = "{$qiniuDomain}/{$key}";
                $dst = "{$tempDir}{$key}";

                // 截取图片名字中包含的'hpl/'4个字符
                $imgs[] = substr($key, 4);

                $handles[] = $dst;
                // 下载七牛图片，最多重试3次
                $downloadResult = $this->downloadPicture($src, $dst);
                if (false === $downloadResult) {
                    return false;
                }
            }
        }

        // 下载pdf
        $pdfSrc = $this->generateUrl('pdfreport', array('orderid'=> $orderId, '_format' => 'pdf'), UrlGeneratorInterface::ABSOLUTE_URL);
        $pdfName = $order->getOrderNo().'.pdf';
        $pdfDst = $tempDir.'hpl/'.$pdfName;
        $downloadPdfResult = $this->downloadPicture($pdfSrc, $pdfDst);
        if (true === $downloadPdfResult) {
            $imgs[] = $pdfName;
            $handles[] = $pdfDst;
        }

        $params = array(
            'access_key' => $this->getParameter('pinganyun_access_key'),
            'bucket_name' => $this->getParameter('pinganyun_bucket'),
            'host_pingan' => $this->getParameter('pinganyun_url'),
            'secret_key' => $this->getParameter('pinganyun_secret_key'),
            'file_path' => $hplDir,
            'imgs' => $imgs,
            'result_path' => $tempDir,
            'result_file_name' => $orderId . '_' . mt_rand(1, 100),
        );

        $javaFile = $this->get('kernel')->getRootDir() . '/../java/' . 'imguploader.jar';

        $json = json_encode($params);
        // 将json中的双引号全部加反斜杠，在最外面加双引号，防止在linux console中解析错误
        $slashJson = '"' . str_replace("\"", "\\\"", $json) . '"';

        exec("java -jar $javaFile $slashJson");

        if (!file_exists($tempDir . $params['result_file_name'])) {
            echo "java 出错。";
            return false;
        }

        $rawResult = file_get_contents($tempDir . $params['result_file_name']);
        $result = json_decode($rawResult);

        if (!count($result->results)) {
            echo 'java上传没结果返回';
            return false;
        } else {
            foreach ($result->results as $item) {
                if (false === $item->success) {
                    echo 'orderId:' . $orderId . 'picture name:' . $item->img . 'failed';
                }
            }
        }

        // 删除图片文件及结果文件
        unlink($tempDir . $params['result_file_name']);

        foreach ($handles as $file) {
            unlink($file);
        }

        return true;
    }

    /**
     * 下载七牛图片，最多重试下载3次
     */
    function downloadPicture($src, $dst)
    {
        $i = 1;
        while ($i <= 3) {
            $result = copy($src, $dst);
            if (true === $result) {
                return true;
            } else {
                echo "Downloading image failed $i times! ";
                $i++;
            }
        }

        return false;
    }

    /**************private function***********/

    /**
     * 判断订单是否需要复审
     * @param Report $report
     */
    private function needRecheck(Report $report, $company)
    {
        //先锋太盟的大于30万的状态要等复核确认后再来决定是否修改成PASS
        if ((Config::COMPANY_HPL === $company || Config::COMPANY_HPL_CBT === $company) && $report->getReport()['field_4012']['value'] >= 300000) {
            return true;
        }

        //平安租赁的要等复核确认后再来决定是否修改成PASS
        if (Config::COMPANY_PINGAN === $company) {
            return true;
        }

        return false;
    }

    //检查结果推送Jpush
    private function addOrderFinishEvent($report)
    {
        $dispatcher = new EventDispatcher();
        $subscriber = $this->get("OrderFinishSubscriber");
        $event = new OrderFinishEvent($report);
        $dispatcher->addSubscriber($subscriber);
        $dispatcher->dispatch(HplEvents::ORDER_FINISH, $event);
    }

    /**
     * 历史估价
     * @param $vars | 参数集合 [brand,series,model,year,type,city] | 品牌 车系 车型 年款 城市 类型
     * @param $page | 页码
     * @param $limit | 页数
     * @return array
     */
    public function getHistorical($vars, $page, $limit)
    {
        /**
         * 需求：查询report表中所有符合（品牌，车系，车型，年款）的记录。
         * 排序：根据当前登录用户的所在地排序（相同所在地车辆排到第一，其次按照字母进行自然排序）进行分页
         */
        //查出符合当前所在地的车辆 - 分成两个table 展示
        switch ($vars['type']) {
            case 1:
                $query = $this->createQueryBuilder("AppBundle:Order", "o")
                    ->join("o.report", "r")
                    ->where("r.status != :status and r.modelId = :modelId")
                    ->setParameter('status', 0)
                    ->setParameter('modelId', $vars['modelId']);
                $result = count($query->getQuery()->getResult());
                break;
            case 2:
                $query = $this->createQueryBuilder("AppBundle:Order", "o")
                    ->join("o.report", "r")
                    ->join("o.loadOfficer", "ol")
                    ->join("ol.city", "lc")
                    ->where("r.status != :status and lc.name = :city and r.modelId = :modelId")
                    ->setParameter('status', 0)
                    ->setParameter('city', $vars['city'])
                    ->setParameter('modelId', $vars['modelId'])
                    ->orderBy('r.examedAt', 'DESC');
                $result = $this->get('knp_paginator')->paginate(
                    $query, /* query NOT result */
                    $page/*page number*/,
                    $limit/*limit per page*/
                );
                break;
            case 3:
                //查询出不同城市的维保信息
                $query = $this->createQueryBuilder("AppBundle:Order", "o")
                    ->join("o.report", "r")
                    ->join("o.loadOfficer", "ol")
                    ->join("ol.city", "lc")
                    ->where("r.status != :status and lc.name != :city and r.modelId = :modelId")
                    ->setParameter('status', 0)
                    ->setParameter('city', $vars['city'])
                    ->setParameter('modelId', $vars['modelId'])
                    ->orderBy('lc.name','ASC')
                    ->addorderBy('r.examedAt','DESC');
                $result = $this->get('knp_paginator')->paginate(
                    $query, /* query NOT result */
                    $page/*page number*/,
                    $limit/*limit per page*/
                );
                break;
            default;
                $result = null;
        }
        return $result;
    }

    /**
     * 计算报告不同阶段所需的时间
     */
    public function getStageTime($reportId)
    {
        $time = [
            Report::STAGE_BASIC => 0,
            Report::STAGE_MODEL => 0,
            Report::STAGE_CONFIG => 0,
            Report::STAGE_SUMMARIZE => 0,
            Report::STAGE_PRICE => 0,
            Report::STAGE_FINISH => 0,
        ];
        $stageLogs = $this->getRepo('AppBundle:StageLog')->findStageLog($reportId);
        $i = 0;
        foreach ($stageLogs as $stageLog) {
            $i++;
            // 取第一条作为参照
            if ($i == 1) {
                $preStage = $stageLog->getStage();
                $start = $stageLog->getCreatedAt();
                continue;
            }

            $stage = $stageLog->getStage();
            $type = $stageLog->getType();
            // 打点计算
            if ($type !== StageLog::TYPE_LOCK) {
                $time[$preStage] += $this->get('util.dateTime')->calculateDiffTime($start, $stageLog->getCreatedAt());
            }

            if($preStage != $stage)
            {
                $preStage = $stage;
            }

            $start = $stageLog->getCreatedAt();
        }
        return $time;
    }

    /**
     * 获取初检和复检报告的差异
     */
    public function getDiffReport($secReport)
    {
        $data = [];
        foreach ($secReport as $key => $value) {
            if($value['diff'] == true) {
                $data[$key]['old'] = $value['old'];
                $data[$key]['new'] = $value['new'];
            }
        }

        return $data;
    }

    /**
     * 获取初检和复检报告的集合
     */
    public function getUnionReport($secReport)
    {
        $data = [];
        foreach ($secReport as $key => $value) {
            if($value['diff'] == true) {
                $data[$key] = $value['new'];
            } else {
                $data[$key] = $value['old'];
            }
        }

        return $data;
    }

    public function checkIfNeedRecheck($report)
    {
        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->findOneBy(['report' => $report]);
        $company = $order->getCompany();
        if ($order->getStatus() === Order::STATUS_RECHECK || $order->getOperateLog()) {
            return false;
        }

        if ($company->getNeedRecheck() === true || $this->getUser()->getNoob() ===true) {
            return true;
        } else {
            return false;
        }
    }
}
