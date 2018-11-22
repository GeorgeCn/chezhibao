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

class TrainingController extends Controller
{
    use DoctrineAwareTrait;

    /**
     * @Route("/training",name="openapi_get_training")
     * @Method("get")
     */
    public function getTrainingAction(Request $request)
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
        $trainings = $this->getRepo('AppBundle:Training')
            ->getTrainings($start, $limit);

        foreach($trainings as $training){
            $tmp['id'] = $training['id'];
            $tmp['title'] = $training['title'];
            $tmp['url'] = $training['url'];
            $tmp['create_date'] = $training['createAt']->format("m-d");
            $data[] = $tmp;
        }
        $ret['data'] = $data;
        return JsonResponse::create($ret);
    }
}
