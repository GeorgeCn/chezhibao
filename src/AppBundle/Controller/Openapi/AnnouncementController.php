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
use AppBundle\Traits\DoctrineAwareTrait;
use AppBundle\Entity\User;

/**
 * 
 * @Route("/openapi/v1")
 */

class AnnouncementController extends Controller
{
    use DoctrineAwareTrait;

    /**
     * @Route("/announce",name="openapi_get_announcement")
     * @Method("get")
     */
    public function getAnnouncementAction(Request $request)
    {
        $data = [];
        $ret['code'] = 0;
        $ret['msg'] = 'success';
        $ret['data'] = $data;
        $userType = $this->getUser()->getType();
        if($userType == User::TYPE_TEMP){
            return JsonResponse::create($ret);
        }

        $start = $request->query->get('start', null);
        $limit = $request->query->get('limit', 10);

        if ($start === null) {
            return JsonResponse::create([
                    'code' => 2,
                    'msg' => "无效参数",
                ]);
        }
        $announcements = $this->getRepo('AppBundle:Announcement')
            ->getAnnouncements($start, $limit);

        foreach($announcements as $announcement){
            $tmp['id'] = $announcement['id'];
            $tmp['title'] = $announcement['title'];
            $tmp['url'] = $announcement['url'];
            $tmp['create_date'] = $announcement['createAt']->format("m-d");
            $data[] = $tmp;
        }
        $ret['data'] = $data;
        return JsonResponse::create($ret);
    }
}
