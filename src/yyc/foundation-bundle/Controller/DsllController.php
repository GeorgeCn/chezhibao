<?php

namespace YYC\FoundationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use YYC\FoundationBundle\Controller\Base\AbstractController;
use YYC\FoundationBundle\Entity\Maintain;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * 大圣来了控制器
 *
 */
class DsllController extends AbstractController
{
    /**
     * 查询首页
     */
    public function indexAction()
    {
        return $this->render('YYCFoundationBundle:dsll:index.html.twig');
    }

    /**
     * 通过ajax向大圣来了查最新数据
     */
    public function newestAjaxAction(Request $request)
    {
        //验证用户是否登录
        $user = $this->isLogin();

        $vin = $request->request->get('vin');
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
        $maintain->setSupplierType(Maintain::TYPE_DSLL);
        $maintain->setOperator($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($maintain);
        $em->flush();
        $id = $maintain->getId();

        // 正式环境用的通知url，需要能在外网访问
        $notifyUrl = $this->generateUrl('yyc_dsll_do', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        //测试环境用ngrok弄的测试url
        // $notifyUrl = "http://symfony.ngrok.cc/project3/web/app_dev.php/dsll/do";

        // 开始异步获取维修记录信息，将插入数据库中的id传过去
        $originalResults = $this->get('yyc_foundation.third.dsll')->getMaintainInfo($vin, "", "", $id, $notifyUrl);
        $results = json_decode($originalResults);
        $status = $results->error_code;
        $msg = $results->error_msg;

        if (0 === $status) {
            return new JsonResponse(array('success' => true, 'msg' => $msg . '已提交查询，处理时间的长短由大圣来了决定, 一般30分钟左右'));
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
            $vars = $maintain;

            return $this->render('YYCFoundationBundle:dsll:show.html.twig', array('vars' => $vars));
        } else {
            return new Response('该记录平台商没有返回结果给我们！');
        }
    }

    /**
     * 品牌列表
     */
    public function brandAction()
    {
        return new Response($this->get('yyc_foundation.third.dsll')->getBrands());
    }

    /**
     * 查询余额
     */
    public function balanceAction()
    {
        return new Response($this->get('yyc_foundation.third.dsll')->getBalance());
    }

    /**
     * 大圣来了异步通知我们时，需要做的事情
     */
    public function doAction(Request $request)
    {
        // 获取大圣以post（form-data）形式推送给我们的内容
        $post = $request->request->all();

        $id = $post['order_id'];

        $maintain = $this->getDoctrine()->getManager()->getRepository('YYCFoundationBundle:Maintain')->find($id);

        if ($maintain) {
            // 只有查询成功才会收费
            if ('QUERY_SUCCESS' === $post['result_status']) {
                $maintain->setReturnAt(new \DateTime());
                $maintain->setStatus(Maintain::STATUS_SUCCESS);
                // 将post的所有内容存放到数据库的这个json字段里面
                $maintain->setResults($post);

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new Response('success');
            } elseif ('QUERY_NO_RECORD' === $post['result_status']) {
                $maintain->setReturnAt(new \DateTime());
                $maintain->setStatus(Maintain::STATUS_SUCCESS);
                // 将post的所有内容存放到数据库的这个json字段里面
                $maintain->setResults($post);
                $maintain->setRemark('查询无记录，未查询到匹配结果');

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new Response('success');
            } elseif ('QUERY_REJECT' === $post['result_status']) {
                $maintain->setStatus(Maintain::STATUS_FAIL);
                // 将post的所有内容存放到数据库的这个json字段里面
                $maintain->setResults($post);
                $maintain->setRemark('查询驳回，提交的图片不清晰或信息有误');

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new Response('success');

            } elseif ('QUERY_FAIL' === $post['result_status']) {
                $maintain->setStatus(Maintain::STATUS_FAIL);
                // 将post的所有内容存放到数据库的这个json字段里面
                $maintain->setResults($post);
                $maintain->setRemark('查询失败，查询系统暂时关闭');

                $em = $this->getDoctrine()->getManager();
                $em->persist($maintain);
                $em->flush();

                return new Response('success');
            } else {
                return new Response('error!');
            }
        } else {
            return new Response('error');
        }
    }

}
