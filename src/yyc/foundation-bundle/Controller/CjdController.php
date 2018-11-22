<?php

namespace YYC\FoundationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use YYC\FoundationBundle\Controller\Base\AbstractController;
use YYC\FoundationBundle\Entity\Maintain;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use \stdClass;

/**
 * 车鉴定控制器
 *
 */
class CjdController extends AbstractController
{
    /**
     * 查询首页
     */
    public function indexAction()
    {
        return $this->render('YYCFoundationBundle:cjd:index.html.twig');
    }

    /**
     * 通过ajax向车鉴定查最新数据
     */
    public function newestAjaxAction(Request $request)
    {
        //验证用户是否登录
        $user = $this->isLogin();
        $vin = strtoupper($request->request->get('vin'));
        $origins = $request->request->get('origins');

        //限制当天只能向大圣查询一次新记录
        $today = $this->getDoctrine()->getManager()->getRepository('YYCFoundationBundle:Maintain')->findTodayByVin($vin);
        if ($today) {
            return new JsonResponse(array('success' => false, 'msg' => '有同事今天已查询过该vin码记录，当天不能重复查询！'));
        }

        //插入一条新的维修记录到数据库中,默认是WAIT状态
        $maintain = new Maintain();
        $maintain->setVin($vin);
        $maintain->setBrandName("");
        $maintain->setOrigins($origins);
        $maintain->setSupplierType(Maintain::TYPE_CJD);
        $maintain->setOperator($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($maintain);
        $em->flush();
        $id = $maintain->getId();

        return new JsonResponse(array('success' => false, 'msg' => "暂停使用"));

        // 开始向车鉴定发起购买报告的请求，将插入数据库中的id传过去
        $originalResults = $this->get('yyc_foundation.third.cjd')->buy($vin, "", $id);
        $results = json_decode($originalResults);
        $status = $results->info->status;
        $msg = $results->info->message;

        if ('1' === $status) {
            // 只有购买成功时车鉴定才会返回订单号
            $orderId = $results->info->orderId;
            // 将车鉴定返回的订单号存起来，以便报告出结果后回调时使用
            $maintain->setOrderId($orderId);
            $em->persist($maintain);
            $em->flush();

            return new JsonResponse(array('success' => true, 'msg' => $msg . '已提交查询，处理时间的长短由车鉴定决定, 一般10分钟左右'));
        } else {
            $maintain->setStatus(Maintain::STATUS_FAIL);
            $maintain->setRemark($msg);
            $em->persist($maintain);
            $em->flush();

            return new JsonResponse(array('success' => false, 'msg' => $msg));
        }
    }

    /**
     * 通过ajax查存放在数据库中最近的历史数据
     */
    public function recentlyAjaxAction(Request $request)
    {
        $vin = $request->request->get('vin');

        $tmp = $this->getDoctrine()->getManager()->getRepository('YYCFoundationBundle:Maintain')->findRecentlyByVin($vin);

        if ($tmp) {
            $encoder = new JsonEncoder();
            $normalizer = new GetSetMethodNormalizer();

            //格式化时间的显示
            $callback = function ($dateTime) {
                return $dateTime instanceof \DateTime
                    ? $dateTime->format('Y-m-d H:i:s')
                    : '';
            };

            $normalizer->setCallbacks(array('createdAt' => $callback));

            $serializer = new Serializer(array($normalizer), array($encoder));
            $data = $serializer->serialize($tmp, 'json');

            return new Response($data);
        } else {
            return new JsonResponse(array('success' => false, 'msg' => '未查询过维修记录'));
        }
    }

    /**
     * 查询维修详情
     */
    public function showAction(Request $request, Maintain $maintain)
    {
        if ($maintain->getResults()) {
            // return new JsonResponse($maintain->getResults());
            // $vars = $maintain->getResults();
            $vars = $maintain;

            return $this->render('YYCFoundationBundle:cjd:show.html.twig', array('vars' => $vars));
        } else {
            return new Response('该记录平台商没有返回结果给我们！');
        }
    }

    /**
     * 查询余额
     */
    public function balanceAction()
    {
        return new Response($this->get('yyc_foundation.third.cjd')->getBalance());
    }

    /**
     * 车鉴定异步通知我们时，需要做的事情
     */
    public function doAction(Request $request)
    {
        // 获取车鉴定以post（form）形式推送给我们的内容
        $orderId = $request->get('orderId');
        $orderStatus = $request->get('orderStatus');

        $maintain = $this->getDoctrine()->getManager()->getRepository('YYCFoundationBundle:Maintain')->findOneBy(array('orderId' => $orderId));

        if ($maintain) {
            //构造返回给车鉴定的json格式信息
            $response = new stdClass;
            $info = new stdClass;
            $info->status = '1';
            $info->message = '成功';
            $response->info = $info;

            // 只有查询成功才会收费
            if ('2' === $orderStatus) {
                $maintain->setReturnAt(new \DateTime());
                $maintain->setStatus(Maintain::STATUS_SUCCESS);

                $originalJsonResults = $this->get('yyc_foundation.third.cjd')->getMaintainInfo($orderId);
                // 需要开启serializer服务
                $arrayResults = $this->get('serializer')->decode($originalJsonResults, 'json');

                // 将结果的所有内容存放到doctrine的json_array字段里面
                $maintain->setResults($arrayResults);

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new JsonResponse($response);
            } elseif ('5' === $orderStatus) {
                $maintain->setStatus(Maintain::STATUS_SUCCESS);
                $maintain->setRemark('查询无记录，未查询到匹配结果');

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new JsonResponse($response);
            } elseif ('6' === $orderStatus) {
                $maintain->setStatus(Maintain::STATUS_FAIL);
                $maintain->setRemark('暂不支持品牌');

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new JsonResponse($response);
            } elseif ('8' === $orderStatus) {
                $maintain->setStatus(Maintain::STATUS_FAIL);
                $maintain->setRemark('品牌系统维护中');

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new JsonResponse($response);
            } elseif ('10' === $orderStatus) {
                $maintain->setStatus(Maintain::STATUS_FAIL);
                $maintain->setRemark('VIN错误');

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new JsonResponse($response);
            } elseif ('11' === $orderStatus) {
                $maintain->setStatus(Maintain::STATUS_FAIL);
                $maintain->setRemark('车牌有误');

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new JsonResponse($response);
            } elseif ('13' === $orderStatus) {
                $maintain->setStatus(Maintain::STATUS_FAIL);
                $maintain->setRemark('发动机有误');

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new JsonResponse($response);
            } else {
                return new Response('error!');
            }
        } else {
            return new Response('error');
        }
    }
}
