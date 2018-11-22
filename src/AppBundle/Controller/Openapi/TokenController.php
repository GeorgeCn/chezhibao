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


/**
 * 
 * @Route("/openapi/v1")
 */

class TokenController extends Controller
{
    /**
     * 获取qiniu的 token
     * @Route("/uploadtoken",name="openapi_token_uploadtoken")
     * @Method("get")
     */
    public function uploadTokenAction(Request $request)
    {
        $qiniu = $this->get("Qiniu");
        $uptoken = $qiniu->getUpToken($this->getParameter("qiniu_bucket"), $this->getParameter("qiniu_prefix"));

        return JsonResponse::create(['code' => 0, 'msg' => '', 'data' =>$uptoken]);
    }
}
