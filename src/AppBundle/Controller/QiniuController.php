<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("qiniu/")
*/
class QiniuController extends Controller
{
    /**
     * @Route("uptoken/", name="qiuniu_uptoken")
     */
    public function upTokenAction(Request $request)
    {
        $qiniu = $this->get("Qiniu");
        $uptoken = $qiniu->getUpToken($this->getParameter("qiniu_bucket"), $this->getParameter("qiniu_prefix"));
        return JsonResponse::create(["uptoken" => $uptoken]);
    }
}