<?php

namespace AppBundle\Controller\Openapi;

use AppBundle\Model\MetadataManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Traits\DoctrineAwareTrait;


/**
 * 
 * @Route("/openapi")
 */

class CodeController extends Controller
{
    use DoctrineAwareTrait;
    /**
     * @Route("/code",name="openapi_code_post")
     * @Method("post")
     */
    public function postCodeAction(Request $request)
    {
        $mobile = $request->request->get('mobile', null);
        if(!$mobile){
            return JsonResponse::create(['code' => 2, 'msg' => '无效参数']);
        }
        //TODO 手机号查询用户
        $users = $this->getRepo('AppBundle:User')->findBy(["mobile"=>$mobile]);
        if(count($users) == 0){
            return JsonResponse::create(['code' => 4, 'msg' => '该手机对应的用户不存在']);
        }
        $sessionKey = "code"."$mobile"."setpassword";
        $SMVerifyCode = $this->get("util.smverifycode");
        $code = $SMVerifyCode->send($sessionKey, $mobile);
        if($code){
            return JsonResponse::create(['code' => 0, 'msg' => 'success']);
        }
        return JsonResponse::create(['code' => 3, 'msg' => '不能频繁获取']);
    }

    /**
     * @Route("/resetpw",name="openapi_reset_password")
     * @Method("post")
     */
    public function resetPasswordAction(Request $request)
    {
        $mobile = $request->request->get('mobile', null);
        $code = $request->request->get('code', null);
        $newpw = $request->request->get('newpw', null);
        if(!$mobile || !$code || !$newpw){
            return JsonResponse::create(['code' => 2, 'msg' => '无效参数']);
        }
        $sessionKey = "code"."$mobile"."setpassword";
        $SMVerifyCode = $this->get("util.smverifycode");
        $check = $SMVerifyCode->validate($sessionKey, "$code");
        if(!$check){
            return JsonResponse::create(['code' => 3, 'msg' => '验证码失效，请重新获取']);
        }
        //TODO 手机号查询用户
        $users = $this->getRepo('AppBundle:User')->findBy(["mobile"=>$mobile]);
        if(count($users) == 0){
            return JsonResponse::create(['code' => 4, 'msg' => '该手机对应的用户不存在']);
        }

        $user = $users[0];
        $username = $user->getUsername();
        $manipulator = $this->get('fos_user.util.user_manipulator');
        $manipulator->changePassword($username, $newpw);

        return JsonResponse::create(['code' => 0, 'msg' => 'success', 'data'=>$username]);
    }
}
