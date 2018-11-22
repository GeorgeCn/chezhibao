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
use AppBundle\Entity\Config;

/**
 * @Route("/openapi")
 */
class InitController extends Controller
{

    /**
     * app 初始化
     * @Route("/init",name="openapi_init_startup")
     * @Method("get")
     */
    public function startupAction()
    {
        $start = [];
        //metadata 有变化，更新版本号
        $start['metadata_version'] = '1.0.3';
        $start['picture_domain'] = $this->getParameter("qiniu_domain");
        $start['app_info'] = 'http://download.youyiche.com/appinfonew.json';

        $start['api_domain'] = $this->getParameter("jiance_domain");
        $start['service_call'] = "021-33685956";
        $start['service_call_jiance'] = "4008216161";
        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' =>$start]);
    }

    /**
     * 这个要废弃的
     * @Route("/metadata",name="openapi_init_metadata")
     * @Method("get")
     */
    public function metaDataAction()
    {
        $company = Config::COMPANY_HPL;
        $orderLogic = $this->get("OrderLogic");
        $ret['backreason_metadata']= $orderLogic->backReasonMetadataForApp();
        $ret['picture_metadata'] = $orderLogic->getPictureMetadatas($company);
        $ret['append_metadata'] = $orderLogic->getAppendMetadata();
        $ret['picture_groups'] = $orderLogic->getPictureGroups();
        // 废弃
        $ret['askquestion_metadata'] = $orderLogic->getAskQuertionMetadata();
        // 废弃
        $ret['title_metadata'] = $orderLogic->getTitleMetadataForApp($company);

        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' => $ret]);
    }

    /**
     * app 初始化
     * @Route("/metadatacustomer",name="openapi_init_metadata_customer")
     * @Method("get")
     */
    public function metaDataCustomer()
    {
        $orderLogic = $this->get("OrderLogic");
        $company = Config::COMPANY_KFCJ;
        $start['backreason_metadata']= $orderLogic->backReasonMetadataForApp();
        $start['append_metadata'] = $orderLogic->getAppendMetadata();
        $start['picture_groups'] = $orderLogic->getPictureGroups();
        $start['askquestion_metadata'] = $orderLogic->getAskQuertionMetadata();

        $start['title_metadata'] = $orderLogic->getTitleMetadataForApp($company);
        $start['picture_metadata'] = $orderLogic->getPictureMetadatas($company);

        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' =>$start]);
    }

    private function checkBOM ($contents) {
        $charset[1] = substr($contents, 0, 1);
        $charset[2] = substr($contents, 1, 1);
        $charset[3] = substr($contents, 2, 1);
        if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) { 
            $contents = substr($contents, 3);
        }
        return $contents;
    }

    /**
     * hpl android 下载
     * @Route("/getapp")
     */
    public function getApp(){
        $app_info = 'http://download.youyiche.com/appinfonew.json';
        $infoJson = file_get_contents($app_info);
        $downloadurl = 'http://download.youyiche.com/ycjc_youyiche_1.4.2.apk';
        $infoJson = $this->checkBOM($infoJson);
        $info = json_decode($infoJson,true);
        if(!empty($info)){
            $downloadurl = $info['android']['downloadurl'];
        }
        return $this->redirect($downloadurl);
    }

    /**
     * hpl android 下载
     * @Route("/getrnapp")
     */
    public function getRnApp(){
        $app_info = 'http://download.youyiche.com/appinfonew.json';
        $infoJson = file_get_contents($app_info);
        $ret = mb_detect_encoding($infoJson, "UTF-8", true);
        if ($ret === false) {
            $infoJson = mb_convert_encoding($infoJson, "UTF-8");
        }
        $downloadurl = 'http://download.youyiche.com/yjc_rn_1.0.0.apk';
        $infoJson = $this->checkBOM($infoJson);
        $info = json_decode($infoJson,true);
        if(!empty($info)){
            $downloadurl = $info['android_rn']['downloadurl'];
        }
        return $this->redirect($downloadurl);
    }
}
