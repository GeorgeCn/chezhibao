<?php

namespace YYC\FoundationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use YYC\FoundationBundle\Controller\Base\AbstractController;
use YYC\FoundationBundle\Entity\Insurance;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use YYC\FoundationBundle\Event\InsuranceCallbackEvent;
use YYC\FoundationBundle\YYCFoundationEvents;

/**
 * 老司机
 * @Security("has_role('ROLE_USER') or request.get('_route') in ['yyc_foundation_lsj_do']")
 *
 */
class LsjController extends AbstractController
{
    /**
     * 查询首页
     */
    public function indexAction()
    {
        return $this->render('YYCFoundationBundle:lsj:index.html.twig');
    }

    /**
     * 通过ajax向老司机查最新数据
     */
    public function newestAjaxAction(Request $request)
    {
        $licence = $request->query->get('licence');
        $vin = $request->query->get('vin');
        $engineNumber = $request->query->get('engineNumber');
        $orderNo = $request->query->get('orderNo');
        $origin = $request->query->get('origin');

        //限制当天只能查询一次新记录
        $today = $this->getDoctrine()->getRepository('YYCFoundationBundle:Insurance')->findTodayByVin($vin);
        if ($today) {
            return new JsonResponse(array('success' => false, 'msg' => '有同事今天已查询过该vin码记录，当天不能重复查询！'));
        }

        //插入一条新的维修记录到数据库中,默认是WAIT状态
        $insurance = new Insurance();
        $insurance->setVin($vin);
        $insurance->setSupplierType(Insurance::TYPE_LSJ);
        $insurance->setOperator($this->getUser());

        if ($orderNo) {
            $insurance->setOrderNo($orderNo);
        }

        if ($origin) {
            $insurance->setOrigin($origin);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($insurance);
        $em->flush();
        $id = $insurance->getId();

        // 正式环境用的通知url，需要能在外网访问
        $notifyUrl = $this->generateUrl('yyc_foundation_lsj_do', array(), UrlGeneratorInterface::ABSOLUTE_URL);

        //测试环境用ngrok弄的测试url
        // $notifyUrl = "http://symfony.ngrok.cc/project3/web/app_dev.php/dsll/do";

        // 开始异步获取维修记录信息，将插入数据库中的id传过去
        $originalResults = $this->get('yyc_foundation.third.lsj')->getInsuranceInfo($vin, $engineNumber, $licence, $notifyUrl);
        $results = json_decode($originalResults);
        $status = $results->Success;
        $msg = $results->Message;

        if (true === $status) {
            // 只有购买成功时对方才会返回订单号
            $orderId = $results->Data;
            // 将返回的订单号存起来，以便报告出结果后回调时使用
            $insurance->setCallbackId($orderId);
            $em->persist($insurance);
            $em->flush();

            return new JsonResponse(array('success' => true, 'msg' => $msg . '已提交查询，处理时间的长短由老司机决定, 一般10分钟左右'));
        } else {
            $insurance->setStatus(Insurance::STATUS_FAIL);
            $insurance->setRemark($msg);
            $em->persist($insurance);
            $em->flush();

            return new JsonResponse(array('success' => false, 'msg' => $msg));
        }
    }

    /**
     * 通过ajax查存放在数据库中最近的历史数据
     */
    public function recentlyAjaxAction(Request $request)
    {
        $vin = $request->query->get('vin');

        $tmp = $this->getDoctrine()->getRepository('YYCFoundationBundle:Insurance')->findRecentlyByVin($vin);

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
     * 查询保险详情
     */
    public function showAction(Request $request, Insurance $insurance)
    {
        if ($insurance->getResults()) {
            $keyWords = ["纵梁", "避震座", "防火墙", "行李箱", "底板", "后窗台", "车顶", "车壳", "A柱", "B柱", "C柱", "D柱", "上边梁", "下边梁", "水箱框架", "龙门架", "气囊", "安全带", "电脑板", "后翼子板", "后叶子板", "地毯", "仪表台", "后围板", "大底边", "座椅", "水泡", "火烧", "翻车", "烧焊", "整形", "切割", "拆装", "发动机", "变速箱", "大修"];
            foreach ($keyWords as $v) {
                $arrayWords[$v] = "<b style='color:red'>".$v."</b>"; 
            } 
            if (Insurance::TYPE_ERP === $insurance->getOrigin()) {
                return $this->render('YYCFoundationBundle:lsj:youyiche_show.html.twig', array('insurance' => $insurance, 'keyWords'=>$arrayWords));
            } else {
                return $this->render('YYCFoundationBundle:lsj:hpl_show.html.twig', array('insurance' => $insurance, 'keyWords'=>$arrayWords));
            }
        } else {
            return new Response('该记录平台商没有返回结果给我们！');
        }
    }

    /**
     * 对方异步通知我们时，需要做的事情
     */
    public function doAction(Request $request)
    {
        // 获取对方以post（form-data）形式推送给我们的内容
        $post = $request->request->all();

        $id = $post['OrderID'];

        $insurance = $this->getDoctrine()->getRepository('YYCFoundationBundle:Insurance')->findOneByCallbackId($id);

        if ($insurance) {
            // 只有查询成功才会收费
            if ('1' === $post['Status']) {
                $insurance->setReturnAt(new \DateTime());
                $insurance->setStatus(Insurance::STATUS_SUCCESS);
                // 将post的所有内容存放到数据库的这个json字段里面
                $insurance->setResults($post);

                $em = $this->getDoctrine()->getManager();
                $em->persist($insurance);
                $em->flush();

                $event = new InsuranceCallbackEvent($insurance->getOrderNo(), $insurance->getId());
                $this->get('event_dispatcher')->dispatch(YYCFoundationEvents::INSURANCE_CALLBACK, $event);

                return new Response('success');
            } else {
                $insurance->setReturnAt(new \DateTime());
                $insurance->setStatus(Insurance::STATUS_FAIL);
                $insurance->setRemark('查询无记录，未查询到匹配结果');
                // 将post的所有内容存放到数据库的这个json字段里面
                $insurance->setResults($post);

                $em = $this->getDoctrine()->getManager();
                $em->persist($insurance);
                $em->flush();

                return new Response('success');
            }
        } else {
            return new Response('error');
        }
    }
}
