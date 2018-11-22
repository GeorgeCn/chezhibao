<?php

namespace AppBundle\Controller\Openapi;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use AppBundle\Model\MetadataManager;

/**
 * 
 * @Route("/openapi/v1")
 */

class MetadataController extends Controller
{
    /**
     * @Route("/metadata")
     */
    public function metaDataAction(Request $request)
    {
        $metadata_version = $request->get('metadata_version', '');
        $appui_version = $request->get('appui_version', '');
        $company = $request->get('company', '');
        $user = $this->getUser();
        $userCompany = $user->getAgencyRels()[0]->getCompany() ? $user->getAgencyRels()[0]->getCompany()->getCompany() : '';
        $orderLogic = $this->get("OrderLogic");
        $mm = $orderLogic->getMetadataManager($company);

        $result = [];
        $result['metadata'] = [];
        $result['appui_content'] = [];
        $ret["version"] = (string)$mm->getVersion();

        if("$metadata_version".$company != "{$ret['version']}".$userCompany){
            //$ret['backreason_metadata']= $orderLogic->backReasonMetadataForApp($company);
            $ret['picture_metadata'] = $orderLogic->getPictureMetadatas($userCompany);
            $ret['append_metadata'] = $orderLogic->getAppendMetadata();
            $ret['picture_groups'] = $orderLogic->getPictureGroups();
            $result['metadata'] = $ret;
        }
        $result['appui_content'] = $this->getAppUicontent();
        if("$appui_version".$company == "{$result['appui_content']['version']}".$userCompany ){
            $result['appui_content'] = [];
        }
        $result['company'] = $userCompany;

        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' => $result]);
    }

    /**
     * @Route("/metadatas")
     */
    public function metaDatasAction(Request $request)
    {
        $ret = [];
        $companyIds = $request->query->get("companyIds");
        foreach ($companyIds as $companyId) {
            $ret[] = $this->get('OrderLogic')->getLayoutMetadatas($companyId);
        }

        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' => $ret]);
    }

    private function getAppUicontent()
    {
        return [
                "version"=>'5',
                "tip"=>[
                    "如实填写信息 ，能给爱车精准估价哦 ",
                    //"车辆报价仅针对您当时提交时的车况，如果交易现场出现报价差异，我们将会安排专业车辆复检师及买家同时在现场再次确认车况，并合理解决出现的问题"
                ],
                "single_data"=>[
                   [
                    "key"=>"extra4",
                    "Title"=>"您的车辆情况",
                    "single"=>[
                            [
                                "title"=>"非常好",
                                "des"=> "车辆没有事故，偶尔刮擦",
                                "key"=> "1",
                                "show"=> 1,
                            ],
                            [
                                "title"=> "很好",
                                "des"=> "车辆有过小事故，或有过钣金，或有过改色",
                                "key"=> "2",
                                "show"=> 2,
                            ],
                            [
                                "title"=> "较好",
                                "des"=> "车辆有过多次事故，但未伤及大梁，或有更换覆盖件（比如门、车轮上方护板等）",
                                "key"=> "3",
                                "show"=> 2,
                            ],
                            [
                                "title"=> "一般",
                                "des"=> "车辆有过大梁整形、或切割，或爆过气囊",
                                "key"=> "4",
                                "show"=> 2,
                            ]
                        ]
                    ],
                    [
                        "key"=>"extra5",
                        "Title"=>"您的车辆机械情况",
                        "single"=>[
                            [
                                "title"=> "非常好",
                                "des"=> "发动机和变速箱无抖动、无异响、无维修",
                                "key"=> "1",
                                "show"=> 0,
                            ],
                            [
                                "title"=> "很好",
                                "des"=> "发动机或者变速箱有轻微抖动，或有轻微异响，但无维修",
                                "key"=> "2",
                                "show"=> 0,
                            ],
                            [
                                "title"=> "较好",
                                "des"=> "发动机或者变速箱有渗油，或换挡有顿挫感，或无维修",
                                "key"=> "3",
                                "show"=> 0,
                            ],
                            [
                                "title"=> "一般",
                                "des"=> "发动机或变速箱有过维修或大修，或机油消耗异常",
                                "key"=> "4",
                                "show"=> 0,
                            ]
                        ]
                    ]
                ],
                "select_data"=>[
                    [
                        "key"=>"extra7",
                        "Title"=>"以下功能是否正常，选中表示正常",
                        "Title2"=>"以下功能模块正常",
                        "Select"=>false,
                        "option"=>[
                                "升窗器",
                                "空调组件",
                                "仪表器",
                                "尾灯",
                            ]
                    ],
                    [
                        "key"=>"extra8",
                        "Title"=>"您是否有加装或改装其他装置",
                        "Title2"=>"加装或改装以下装置",
                        "Select"=>false,
                        "option"=>[
                            "真皮座椅",
                            "倒车雷达",
                            "倒车影像导航",
                            "改装轮毂",
                            "加装后排头枕屏",
                            "车辆有选配单"
                        ]
                    ],
                ],
                "agree"=>"我已承诺上述填写内容的真实性，如果在车辆后续交易检测中存在隐瞒事项，愿意重新对车辆价值进行评估，并承担由此造成的损失。"
            ]
        ;
    }

    /**
     * @Route("/checkpicture")
     */
    public function checkPictureData()
    {
        $checkPictureData = [
            'version'=>"1",
            'enable'=>true,
            'detection_interval'=>[
                ['brightness'=>80,'definition'=>40],
                ['brightness'=>140,'definition'=>20],
                ['brightness'=>210,'definition'=>14]
            ]
        ]
    ;
        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' => $checkPictureData]);
    }
}