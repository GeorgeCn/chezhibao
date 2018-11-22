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
use AppBundle\Entity\User;
use AppBundle\Entity\Config;
use AppBundle\Traits\DoctrineAwareTrait;

/**
 * 
 * @Route("/openapi/v1")
 */

class UserCenterController extends Controller
{
    use DoctrineAwareTrait;
    /**
     * 获取用户基本信息(将被弃用)
     * @Route("/self",name="openapi_usercenter_getuserinfo")
     * @Method("get")
     */
    public function getUserInfoAction(Request $request)
    {
        $user = $this->getUser();
        if(!$user){
            return JsonResponse::create(['code' => 2, 'msg' => '用户不存在']);
        }
        $userInfo['id'] = $user->getId();
        $userInfo['name'] = $user->getName();
        $userInfo['username'] = $user->getUsername();
        $userInfo['email'] = $user->getEmail();
        $userInfo['mobile'] = $user->getMobile()?:"";
        $userInfo['pic'] = '';
        $userInfo['userauth'] = $user->getName() == "apptest";

        $type = $user->getType();
        $company = $user->getAgencyRels()[0]->getCompany()->getCompany();
        $orderLogic = $this->get("OrderLogic");
        $mm = $orderLogic->getMetadataManager($company);

        //获取 company 对应的 config
        $config = $this->getRepo('AppBundle:Config')->findOneBy(['company' => $company]);
        $version = $config ? $config->getVersion() : 0 ;
        $version = (string)$version;
        $version_mm = (string)$mm->getVersion();

        $userInfo['user_metadata_version'] = $version.$version_mm;
        $userInfo['user_metadata_url'] = "/openapi/v1/metadata_company";
        $userInfo['company_config'] = $user->getAgencyRels()[0]->getCompany()->getCompany()."_";
        if($company == Config::COMPANY_HPL){
            $userInfo['user_metadata_url'] = "/openapi/metadata";
            $userInfo['company_config'] = $company;
        }
        if($type == User::TYPE_TEMP){
            $userInfo['user_type'] = 4;
            $userInfo['user_metadata_url'] = "/openapi/metadatacustomer";
        }else{
            $userInfo['user_type'] = 0;
        }
        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' =>$userInfo]);
    }

    /**
     * 获取用户详情信息
     * @Route("/userDetail", name="openapi_usercenter_get_user_detail")
     * @Method("get")
     */
    public function getUserDetailAction(Request $request)
    {
        $user = $this->getUser();
        if(!$user){
            return JsonResponse::create(['code' => 2, 'msg' => '用户不存在']);
        }
        $userInfo['id'] = $user->getId();
        $userInfo['name'] = $user->getName();
        $userInfo['username'] = $user->getUsername();
        $userInfo['email'] = $user->getEmail();
        $userInfo['mobile'] = $user->getMobile()?:"";
        $userInfo['pic'] = '';
        $userInfo['userauth'] = $user->getName() == "apptest";
        $userInfo['companies_info'] = [];

        foreach ($user->getAgencyRels() as $agencyRel) {
            $ret['companyId'] = $agencyRel->getCompany()->getId();
            $ret['companyName'] = $agencyRel->getCompany()->getCompany();
            $ret['company_metadata_version'] = $this->get('OrderLogic')->getCompanyMetaVersion($ret['companyName']);
            $userInfo['companies_info'][] = $ret;
        }

        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' =>$userInfo]);
    }

    /**
     * app 初始化
     * @Route("/metadata_company",name="openapiv1_metadata_company")
     * @Method("get")
     */
    public function metaDataAction()
    {
        $user = $this->getUser();
        $company = $user->getAgencyRels()[0]->getCompany()->getCompany();
        $bf = $this->get('app.business_factory');
        $fieldPolicy = $bf->getFieldPolicy($company);
        $orderLogic = $this->get("OrderLogic");
        $start['backreason_metadata']= $orderLogic->backReasonMetadataForApp($company);
        $start['picture_metadata'] = $orderLogic->getPictureMetadatas($company);
        $start['append_metadata'] = $orderLogic->getAppendMetadata();
        $start['picture_groups'] = $orderLogic->getPictureGroups();
        $start['title_metadata'] = $orderLogic->getTitleMetadataForApp($company);
        $start['askquestion_metadata'] = $orderLogic->getAskQuertionMetadata();
        $start['field_policy'] = $fieldPolicy;

        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' =>$start]);
    }

    /**
     * 根据公司id获取对应的meta
     * @Route("/get_metadata/{id}", name="openapiv1_get_metadata")
     * @Method("get")
     */
    public function getMetaDataAction(Request $request, Config $config)
    {
        $companyName = $config->getCompany();
        $bf = $this->get('app.business_factory');
        $fieldPolicy = $bf->getFieldPolicy($companyName);
        $orderLogic = $this->get("OrderLogic");
        $start['backreason_metadata']= $orderLogic->backReasonMetadataForApp($companyName);
        $start['picture_metadata'] = $orderLogic->getPictureMetadatas($companyName);
        $start['append_metadata'] = $orderLogic->getAppendMetadata();
        $start['picture_groups'] = $orderLogic->getPictureGroups();
        $start['title_metadata'] = $orderLogic->getTitleMetadataForApp($companyName);
        $start['askquestion_metadata'] = $orderLogic->getAskQuertionMetadata();
        $start['field_policy'] = $fieldPolicy;

        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' =>$start]);
    }

    /**
     * app 访问页面统计
     * @Route("/addnewpv",name="openapiv1_addnewpv")
     * @Method("post")
     */
    public function addNewPv(Request $request)
    {
        $params['user'] = $this->getUser();
        $params['origin'] = $request->request->get('origin', '');
        $params['version'] = $request->request->get('version', '');
        $params['deepnum'] = $request->request->get('deepnum', 0);
        $params['draft'] = $request->request->get('draft', 0);
        $params['ip'] = $request->getClientIp();

        $params['origin'] = $params['origin'] ?:  "app";
        $params['version'] = $params['version'] ?:  "na";

        $repo = $this->getRepo('AppBundle:PageView');
        $repo->create($params);
        return JsonResponse::create(['code' => 0, 'msg' => 'success', 'data' =>'']);
    }

}
