<?php

namespace AppBundle\Controller;

use AppBundle\Event\HplEvents;
use AppBundle\Event\OrderSubmitEvent;
use AppBundle\Model\MetadataManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Business\OrderLogic;
use AppBundle\Entity\Order;
use AppBundle\Entity\Report;
use AppBundle\Entity\OrderBack;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Traits\DoctrineAwareTrait;
use AppBundle\Entity\User;
use AppBundle\Entity\Config;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 *
 * @Route("/order")
 */
class OrderController extends Controller
{
    /**
     * 新增订单
     * @Security("has_role('ROLE_LOADOFFICER') or has_role('ROLE_LOADOFFICER_MANAGER')")
     * @Route("/new",name="order_new")
     */
    public function newAction(Request $request)
    {
        $orderLogic = $this->get("OrderLogic");
        $order = $orderLogic->createOrder($this->getUser());

        return $this->redirectToRoute("order_edit", ["id" => $order->getId()]);
    }

    /**
     * 查看详情页面，主要是图片的展示
     *
     * @Route("/show/{id}",name="order_show")
     */
    public function showAction(Order $order, Request $request)
    {
        $userType = $order->getLoadOfficer()->getType();
        $orderLogic = $this->get("OrderLogic");
        list($metadatas, $append_metadata, $groups) = $orderLogic->getMetadatas(true, $getGroups = true, $order->getCompany()->getCompany());
        // 覆写meta顺序及新增group，特殊写死处理
        $metadatas = $orderLogic->getSortedMetadatas($metadatas);
        $groups = ['证件照','车况', '车型图', '附加'];

        $vars["order"] = $order;
        $vars["metadatas"] = $metadatas;
        $vars["append_metadata"] = $append_metadata;
        $vars['groups'] = $groups;
        $vars['fieldDisplay'] = $this->get('app.business_factory')->getFieldPolicy($order->getCompany()->getCompany());

        return $this->render('order/show.html.twig', $vars);
    }

    /**
     * 编辑订单
     * @Security("has_role('ROLE_LOADOFFICER') or has_role('ROLE_LOADOFFICER_MANAGER')")
     * @Route("/edit/{id}",name="order_edit")
     * @ParamConverter("order", class="AppBundle:Order")
     */
    public function editAction(Order $order, Request $request)
    {
        $this->checkOrderOwner($order, Order::STATUS_EDIT);
        $orderLogic = $this->get("OrderLogic");
        $qiniu = $this->get("Qiniu");
        $vars = [
            "order" => $order,
            "uptoken" => $qiniu->getUpToken($this->getParameter("qiniu_bucket"), $this->getParameter("qiniu_prefix")),
        ];

        // 根据不同的公司获取不同的额外meata，web页面端暂时不需要编辑额外的meta
        $company = $this->getUser()->getCompany();
        // $vars['extraMatadatas'] = $orderLogic->getOrderExtraMetadatas($company);
        $vars['extraMatadatas'] = false;

        $vars['fieldDisplay'] = $this->get('app.business_factory')->getFieldPolicy($company);

        $back = $order->getLastBack();
        if (empty($back)) {
            // 过滤掉append的metadata，因为append只有在退回编辑的时候用到。
            $vars['metadatas'] = $orderLogic->getMetadatas($withAppend = false, $getGroups = true)[0];
            $vars['groups'] = $orderLogic->getMetadatas($withAppend = false, $getGroups = true)[1];

            return $this->render('order/edit.html.twig', $vars);
        }

        $vars["reason_metadatas"] = $orderLogic->matchBackReasonMetas($back->getReason());
        $vars["main_reason"] = $back->getMainReason();
        list($metadatas, $append_metadata, $groups) = $orderLogic->getMetadatas(true, $getGroups = true);
        $vars["metadatas"] = $metadatas;
        $vars["append_metadata"] = $append_metadata;
        $vars['groups'] = $groups;
        $vars["append_key"] = "append_" . $orderLogic->findAppendKey($order);

        return $this->render('order/append.html.twig', $vars);
    }

    /**
     * @Security("has_role('ROLE_LOADOFFICER') or has_role('ROLE_LOADOFFICER_MANAGER')")
     * @Route("/submit/{id}",name="order_submit")
     * @Method("post")
     * @ParamConverter("order", class="AppBundle:Order")
     */
    public function submitAction(Order $order, Request $request)
    {
        $this->checkOrderOwner($order, Order::STATUS_EDIT);
        $posts = $request->request->all();
        $orderLogic = $this->get("OrderLogic");
        $orderLogic->updateOrder($order, $posts, true);

        return $this->redirectToRoute("order_submitted_list");
    }

    /**
     * 草稿箱订单列表
     * @Security("has_role('ROLE_LOADOFFICER') or has_role('ROLE_LOADOFFICER_MANAGER')")
     * @Route("/draft",name="order_draft_list")
     */
    public function draftListAction(Request $request)
    {
        $title = '草稿箱';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findOrderDraft($this->getUser()->getId(), true);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/draft_list.html.twig', array(
            'title' => $title,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
        ));
    }

    /**
     * 已提交(待审核状态)订单列表
     * @Security("has_role('ROLE_LOADOFFICER') or has_role('ROLE_LOADOFFICER_MANAGER')")
     * @Route("/submitted",name="order_submitted_list")
     */
    public function submittedListAction(Request $request)
    {
        $title = '已提交';

        $company = $this->getUser()->getCompany();

        $fieldDisplay = $this->get('app.business_factory')->getFieldPolicy($company);

        $vars['mixed'] = $request->query->get('vars')['mixed'];
        $vars['status'] = $request->query->get('vars')['status'];
        $vars['startDate'] = $request->query->get('vars')['startDate'];
        $vars['endDate'] = $request->query->get('vars')['endDate'];
        $vars['dateType'] = $request->query->get('vars')['dateType'];
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findOrderSubmitted($this->getUser()->getId(), $vars['mixed'], $vars['status'], $vars['startDate'], $vars['endDate'], true);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit /*limit per page*/
        );

        return $this->render('order/submitted_list.html.twig', array(
            'title' => $title,
            'fieldDisplay' => $fieldDisplay,
            'vars' => $vars,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
        ));
    }

    /**
     * 已退回订单列表
     * @Security("has_role('ROLE_LOADOFFICER') or has_role('ROLE_LOADOFFICER_MANAGER')")
     * @Route("/back",name="order_back_list")
     */
    public function backListAction(Request $request)
    {
        $title = '已退回';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findOrderBack($this->getUser()->getId(), true);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/back_list.html.twig', array(
            'title' => $title,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
            'orderLogic' => $this->get("OrderLogic"),
        ));
    }

    /**
     * 草稿箱逻辑删除(disable为1)
     * @Security("has_role('ROLE_LOADOFFICER') or has_role('ROLE_LOADOFFICER_MANAGER')")
     * @Route("/draft/{id}/disable",name="order_draft_disable")
     */
    public function draftDisableAction($id)
    {
        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->find($id);

        if ($order->getLoadOfficer() != $this->getUser()) {
            throw $this->createAccessDeniedException('这不是你的检测订单！');
        }

        $order->setDisable(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $this->addFlash(
            'notice',
            '撤单成功'
        );

        return $this->redirectToRoute('order_draft_list');
    }

    /**
     * 已退回逻辑删除(disable为1)
     * @Security("has_role('ROLE_LOADOFFICER') or has_role('ROLE_LOADOFFICER_MANAGER')")
     * @Route("/back/{id}/disable",name="order_back_disable")
     */
    public function backDisableAction($id)
    {
        $order = $this->getDoctrine()->getRepository('AppBundle:Order')->find($id);

        if ($order->getLoadOfficer() != $this->getUser()) {
            throw $this->createAccessDeniedException('这不是你的检测订单！');
        }

        $order->setDisable(1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($order);
        $em->flush();

        $this->addFlash(
            'notice',
            '删除成功'
        );

        return $this->redirectToRoute('order_back_list');
    }

    /**
     * 订单任务列表
     * @Security("has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/task_list",name="order_task_list")
     */
    public function taskListAction(Request $request)
    {
        $title = '任务列表';

        $vars['type'] = $request->query->get('vars')['type'];
        $vars['orderStatus'] = $request->query->get('vars')['orderStatus'];
        $vars['stage'] = $request->query->get('vars')['stage'];
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findTask(true, $vars['type'], $vars['orderStatus'], $vars['stage']);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/task_list.html.twig', array(
            'title' => $title,
            'vars' => $vars,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
            'orderLogic' => $this->get('OrderLogic'),
        ));
    }

    /**
     * 插队
     * @Security("has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/order_jump/{id}", name="order_jump")
     */
    public function jumpAction(Request $request, Order $order)
    {
        $order->setJump(true);
        $order->setJumpedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->addFlash(
            'notice',
            '插队成功'
        );

        return $this->redirectToRoute('order_task_list');
    }

    /**
     * 审核师可以看到所有退回的订单列表
     * @Security("has_role('ROLE_EXAMER')")
     * @Route("/examer/back",name="order_examer_back_list")
     */
    public function examerBackListAction(Request $request)
    {
        $title = '已退回';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $vars['mixed'] = $request->query->get('vars')['mixed'];
        $vars['company'] = $request->query->get('vars')['company'];
        // 1代表当天
        $vars['dateType'] = $request->query->get('vars')['dateType'] ?: 1;

        if ($vars['dateType'] == 1) {
            $vars['startDate'] = date('Y-m-d');
            $vars['endDate'] = date('Y-m-d');
        } else {
            $vars['startDate'] = $request->query->get('vars')['startDate'];
            $vars['endDate'] = $request->query->get('vars')['endDate'];
        }

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findOrderExamerBack(null, true, $vars['mixed'], $vars['company'],$vars['startDate'],$vars['endDate']);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/examer_back_list.html.twig', array(
            'title' => $title,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
            'orderLogic' => $this->get("OrderLogic"),
            'vars' => $vars,
        ));
    }

    /**
     * 复审专员可以看到所有退回的订单列表 (此方法与上面方法逻辑一样)
     * @Security("has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/examerRecheck/back", name="order_examer_recheck_back_list")
     */
    public function examerRecheckBackListAction(Request $request)
    {
        $title = '已退回';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $vars['mixed'] = $request->query->get('vars')['mixed'];
        $vars['company'] = $request->query->get('vars')['company'];
        // 1代表当天
        $vars['dateType'] = $request->query->get('vars')['dateType'] ?: 1;

        if ($vars['dateType'] == 1) {
            $vars['startDate'] = date('Y-m-d');
            $vars['endDate'] = date('Y-m-d');
        } else {
            $vars['startDate'] = $request->query->get('vars')['startDate'];
            $vars['endDate'] = $request->query->get('vars')['endDate'];
        }

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findOrderExamerBack(null, true, $vars['mixed'], $vars['company'],$vars['startDate'],$vars['endDate']);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/examer_recheck_back_list.html.twig', array(
            'title' => $title,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
            'orderLogic' => $this->get("OrderLogic"),
            'vars' => $vars,
        ));
    }

    /**
     * @Route("/examer/historycsv",name="examerhistory_csv")
     */
    public function examerHistoryCsvAction(Request $request)
    {
        $title = "已退回";

        $vars['mixed'] = $request->query->get('vars')['mixed'];
        $vars['company'] = $request->query->get('vars')['company'];
        // 1代表当天
        $vars['dateType'] = $request->query->get('vars')['dateType'] ?: 1;

        if ($vars['dateType'] == 1) {
            $vars['startDate'] = date('Y-m-d');
            $vars['endDate'] = date('Y-m-d');
        } else {
            $vars['startDate'] = $request->query->get('vars')['startDate'];
            $vars['endDate'] = $request->query->get('vars')['endDate'];
        }

        $em = $this->get('doctrine')->getManager();
        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findOrderExamerBack(null, true, $vars['mixed'], $vars['company'],$vars['startDate'],$vars['endDate']);

        $paginator = new Paginator($query);
        $result_count = $paginator->count();
        $fieldViewName = [
            'orderNo' => '评估单号',
            'mainReason' => '退回原因',
            'vin' => '车架号',
            'status' => '单据状态',
            'brand' => '品牌',
            'series' => '车系',
            // 'sytle' => '年款',
            'model' => '车型',
            'loadOfficer' => '提交人',
            'company' => '金融公司',
            'agency' => '经销商',
            'submitedAt' => '提交时间',
            'createdAt' => '退回时间',
            'examer' => '审核人',
            'examedAt' => '终审时间',
            'reason' => '具体原因',
        ];
        $orderLogic = $this->get('orderLogic');
        $metaReason = $orderLogic->backstageReasonKeyMetadata();
        foreach ($metaReason as $k => $v) {
            $fieldViewName[$k] = $v['display'];
        }

        $get_result = function($paginator) use(&$em) {
            gc_enable();
            $result = [];
            $orderLogic = $this->get('orderLogic');
            foreach ($paginator as $k=>$order) {
                $result[$k]['orderNo'] = $order->getOrderNo();
                $result[$k]['mainReason'] = trim($order->getLastBack()->getMainReason());
                $result[$k]['vin'] = $order->getReport()->getVin();
                $status = $order->getReport()->getStatus();
                switch($status) {
                    case Report::STATUS_WAIT : $result[$k]['status'] = '已提交';break;
                    case Report::STATUS_WAIT : $result[$k]['status'] = '通过';break;
                    case Report::STATUS_WAIT : $result[$k]['status'] = '拒绝';break;
                    default : $result[$k]['status'] = null;break;
                }
                $result[$k]['brand'] = $order->getReport()->getBrand();
                $result[$k]['series'] = $order->getReport()->getSeries();
                // $result[$k]['style'] = $order->getReport()->getReport() ? $order->getReport()->getReport()['field_2040']['value'] : "";;
                $result[$k]['model'] = $order->getReport()->getModel();
                $result[$k]['loadOfficer'] = $order->getLoadOfficer()->getName();
                $result[$k]['company'] = $order->getCompany()->getCompany();
                $result[$k]['agency'] = $order->getAgencyName();
                $result[$k]['submitedAt'] = $order->getSubmitedAt()->format("Y-m-d H:i:s");
                $result[$k]['createdAt'] = $order->getLastBack()->getCreatedAt()->format("Y-m-d H:i:s");
                $result[$k]['examer'] = $order->getReport()->getExamer()->getName();
                $examedAt = $order->getReport()->getExamedAt();
                $result[$k]['examedAt'] = empty($examedAt) ? null : $examedAt->format("Y-m-d H:i:s");
                $result[$k]['reason'] = null;
                $reasons = $orderLogic->matchBackstageReasonMetas($order->getLastBack()->getReason());
                foreach ($reasons as $value) {
                        $result[$k][$value->key] = $value->value;
                }
            }

            $em->getConnection()->getConfiguration()->setSQLLogger(null);
            $em->clear();
            gc_collect_cycles();

            return $result;
        };

        return $this->get('CsvLogic')->queryExportCSV($result_count, $query, $get_result, '已退回', $fieldViewName);
    }

    /**
     * 审核师主管审核退回的单子
     * @Security("has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/back/audit", name="back_audit_list")
     */
    public function backAuditAction(Request $request)
    {
        $title = '退单历史';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;
        $vars['mixed'] = $request->query->get('vars')['mixed'];
        // 1代表当天
        $vars['dateType'] = $request->query->get('vars')['dateType'] ?: 1;

        if ($vars['dateType'] == 1) {
            $vars['startDate'] = date('Y-m-d');
            $vars['endDate'] = date('Y-m-d');
        } else {
            $vars['startDate'] = $request->query->get('vars')['startDate'];
            $vars['endDate'] = $request->query->get('vars')['endDate'];
        }

        $query = $this->getDoctrine()->getRepository('AppBundle:OrderBack')
            ->findBackAudit($vars['mixed'],$vars['startDate'],$vars['endDate']);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/back_audit_list.html.twig', array(
            'title' => $title,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
            'orderLogic' => $this->get("OrderLogic"),
            'vars' => $vars,
        ));
    }

    /**
     * @Route("/back/historycsv",name="backhistory_csv")
     */
    public function backHistoryCsvAction(Request $request)
    {
        $title = "退单历史";

        $vars['mixed'] = $request->query->get('vars')['mixed'];
        // 1代表当天
        $vars['dateType'] = $request->query->get('vars')['dateType'] ?: 1;

        if ($vars['dateType'] == 1) {
            $vars['startDate'] = date('Y-m-d');
            $vars['endDate'] = date('Y-m-d');
        } else {
            $vars['startDate'] = $request->query->get('vars')['startDate'];
            $vars['endDate'] = $request->query->get('vars')['endDate'];
        }

        $em = $this->get('doctrine')->getManager();
        $query = $this->getDoctrine()->getRepository('AppBundle:OrderBack')
            ->findBackAudit($vars['mixed'],$vars['startDate'],$vars['endDate']);

        $paginator = new Paginator($query);
        $result_count = $paginator->count();
        $fieldViewName = [
            'orderNo' => '评估单号',
            'mainReason' => '退回原因',
            'vin' => '车架号',
            'status' => '单据状态',
            'brand' => '品牌',
            'series' => '车系',
            // 'sytle' => '年款',
            'model' => '车型',
            'loadOfficer' => '提交人',
            'company' => '金融公司',
            'agency' => '经销商',
            'submitedAt' => '提交时间',
            'createdAt' => '退回时间',
            'examer' => '审核人',
            'examedAt' => '终审时间',
            'reason' => '具体原因',
        ];
        $orderLogic = $this->get('orderLogic');
        $metaReason = $orderLogic->backstageReasonKeyMetadata();
        foreach ($metaReason as $k => $v) {
            $fieldViewName[$k] = $v['display'];
        }

        $get_result = function($paginator) use(&$em) {
            gc_enable();
            $result = [];
            $orderLogic = $this->get('orderLogic');
            foreach ($paginator as $k=>$orderBack) {
                $result[$k]['orderNo'] = $orderBack->getExamOrder()->getOrderNo();
                $result[$k]['mainReason'] = trim($orderBack->getMainReason());
                $result[$k]['vin'] = $orderBack->getExamOrder()->getReport()->getVin();
                $status = $orderBack->getExamOrder()->getReport()->getStatus();
                switch($status) {
                    case Report::STATUS_WAIT : $result[$k]['status'] = '已提交';break;
                    case Report::STATUS_WAIT : $result[$k]['status'] = '通过';break;
                    case Report::STATUS_WAIT : $result[$k]['status'] = '拒绝';break;
                    default : $result[$k]['status'] = null;break;
                }
                $result[$k]['brand'] = $orderBack->getExamOrder()->getReport()->getBrand();
                $result[$k]['series'] = $orderBack->getExamOrder()->getReport()->getSeries();
                // $result[$k]['style'] = $order->getReport()->getReport() ? $order->getReport()->getReport()['field_2040']['value'] : "";;
                $result[$k]['model'] = $orderBack->getExamOrder()->getReport()->getModel();
                $result[$k]['loadOfficer'] = $orderBack->getExamOrder()->getLoadOfficer()->getName();
                $result[$k]['company'] = $orderBack->getExamOrder()->getCompany()->getCompany();
                $result[$k]['agency'] = $orderBack->getExamOrder()->getAgencyName();
                $result[$k]['submitedAt'] = $orderBack->getExamOrder()->getSubmitedAt()->format("Y-m-d H:i:s");
                $result[$k]['createdAt'] = $orderBack->getExamOrder()->getCreatedAt()->format("Y-m-d H:i:s");
                $result[$k]['examer'] = $orderBack->getExamOrder()->getReport()->getExamer()->getName();
                $examedAt = $orderBack->getExamOrder()->getReport()->getExamedAt();
                $result[$k]['examedAt'] = empty($examedAt) ? null : $examedAt->format("Y-m-d H:i:s");
                $result[$k]['reason'] = null;
                $reasons = $orderLogic->matchBackstageReasonMetas($orderBack->getReason());
                foreach ($reasons as $value) {
                        $result[$k][$value->key] = $value->value;
                }
            }

            $em->getConnection()->getConfiguration()->setSQLLogger(null);
            $em->clear();
            gc_collect_cycles();

            return $result;
        };

        return $this->get('CsvLogic')->queryExportCSV($result_count, $query, $get_result, '退单历史', $fieldViewName);
    }

    /**
     * 退回单子详情
     * @Security("has_role('ROLE_EXAMER') or has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/back/show/{id}", name="back_show")
     */
    public function backShowAction(Request $request, OrderBack $orderBack)
    {
        $order = $orderBack->getExamOrder();
        $vars['order'] = $order;
        $orderLogic = $this->get("OrderLogic");
        $vars['orderLogic'] = $orderLogic;
        $back = $orderBack;
        $vars["reason_metadatas"] = $orderLogic->matchBackReasonMetas($back->getReason());
        $vars["main_reason"] = $back->getMainReason();
        
        
        //获取该订单的相关公司配置信息及meta字段是否隐藏信息
        $orderCompanyName = $order->getCompany()->getCompany();
        $fieldDisplay = $this->get('app.business_factory')->getFieldPolicy($orderCompanyName);
        $vars['fieldDisplay'] = $fieldDisplay;

        list($metadatas, $append_metadata, $groups) = $orderLogic->getMetadatas(true, $getGroups = true, $orderCompanyName);
        // 覆写meta顺序及新增group，特殊写死处理
        $metadatas = $orderLogic->getSortedMetadatas($metadatas);
        $groups = ['证件照','车况', '车型图', '附加'];

        $vars["metadatas"] = $metadatas;
        $vars["append_metadata"] = $append_metadata;
        $vars['groups'] = $groups;
        
        return $this->render('order/back_show.html.twig', $vars);
    }

    /**
     * 异常订单查询
     * @Security("has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/abnormal", name="order_abnormal")
     */
    public function abnormalAction(Request $request)
    {
        $title = '异常处理';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $vars['orderNo'] = $request->query->get('vars')['orderNo'];

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findOrderAbnormal($vars['orderNo'], true);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/abnormal.html.twig', array(
            'title' => $title,
            'vars' => $vars,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
        ));
    }

    /**
     * 异常订单处理
     * @Security("has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/abnormal/{id}/handle", name="order_abnormal_handle")
     */
    public function abnormalHandleAction(Order $order, Request $request)
    {
        if (Order::STATUS_DONE === $order->getStatus() && true !== $order->getDisable()) {
            $em = $this->getDoctrine()->getManager();

            $report = $order->getReport();

            $newOrder = clone $order;
            $newReport = clone $report;

            //将新报告的状态还原成等待审核状态
            $newReport->setStatus(Report::STATUS_WAIT);
            $newReport->setExamer($this->getUser());
            $newReport->setCreatedAt(null);
            $newReport->setExamedAt(null);
            $newReport->setHplExaming(false);
            $newReport->setLocked(false);

            $em->persist($newReport);
            $em->flush();

            //将新订单的状态还原为已提交,提交时间为当前时间，并关联新报告的id,同时记录操作日志
            $newOrder->setReport($newReport);
            $newOrder->setLastBack(null);
            $secData = $newReport->getSecReport();
            if (!empty($secData)) {
                $newOrder->setStatus(Order::STATUS_RECHECK);
            } else {
                $newOrder->setStatus(Order::STATUS_EXAM);
            }
            
            $newOrder->setSubmitedAt(new \DateTime());
            $newOrder->setOperateLog('原评估单号:' . $order->getOrderNo() . ',操作者id:' . $this->getUser()->getId() . ',操作者姓名:' . $this->getUser()->getName() . ',操作时间:' . date('Y-m-d H:i:s'));

            //将原订单逻辑删除
            $order->setDisable(1);
            $order->setFork($newOrder);
            $em->persist($newOrder);
            $em->flush();

            $secData = $newReport->getSecReport();
            if(!empty($secData)) {
                $newReport->setCreatedAt(new \DateTime());
                $newReport->setExamedAt(new \DateTime());
                $em->flush();

                return $this->redirectToRoute('task_confirm', array('id' => $newReport->getId()));
            } else {
                return $this->redirectToRoute('work_check', array('id' => $newOrder->getId()));
            }
        } else {
            throw $this->createAccessDeniedException('订单状态异常，请核实！');
        }
    }

    /**
     * 高价车复审列表
     * @Security("has_role('ROLE_EXAMER_HPL') or has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/recheck",name="order_recheck_list")
     */
    public function RecheckListAction(Request $request)
    {
        $title = '待审核';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        if ($this->isGranted('ROLE_EXAMER_MANAGER')) {
            $companyName = null;
        } else {
            // 拥有ROLE_EXAMER_HPL角色只能看到和自己公司名称一样的记录
            $companyName = $this->getUser()->getAgencyRels()[0]->getCompany()->getCompany();
        }

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findRecheck(true, $companyName);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/recheck_list.html.twig', array(
            'title' => $title,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
        ));
    }

    /**
     * @Security("has_role('ROLE_EXAMER')")
     * @Route("/work_check/{id}", name="work_check")
     * @ParamConverter("order", class="AppBundle:Order")
     */
    public function workCheck(Order $order)
    {
        if ($this->get('OrderLogic')->allowAudit($order) === false) {
            throw $this->createAccessDeniedException('当前单子可能是复制出来的单子，等父单子审核完才可审子单子！');
        }
        // 复制父单子数据
        $order = $this->get('OrderLogic')->copyReport($order);
        $report = $order->getReport();

        if (!$report) {
            $report = $this->get('OrderLogic')->associateOrderReport($order);
        }

        return $this->redirect($this->generateUrl('task_check', ['id' => $report->getId()]));
    }

    /**
     * 已复核的订单列表
     * @Security("has_role('ROLE_EXAMER') or has_role('ROLE_EXAMER_HPL')")
     * @Route("/checked",name="order_checked_list")
     */
    public function checkedListAction(Request $request)
    {
        $title = '已审核';
        $companyRepo = $this->getDoctrine()->getRepository('AppBundle:Config');

        $company = $this->getUser()->getAgencyRels()[0]->getCompany()->getCompany();
        $showExamer = $this->get('app.business_factory')->getFieldPolicy($company)['showExamer'];
        $showMaintain = $this->get('app.business_factory')->getFieldPolicy($company)['maintain'];
        $showReport = $this->get('app.business_factory')->getFieldPolicy($company)['report'];

        $bf = $this->get('app.business_factory');

        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $vars['mixed'] = $request->query->get('vars')['mixed'];

        if ($this->isGranted('ROLE_EXAMER')) {
            $vars['company'] = $request->query->get('vars')['company'];
        }

        $vars['status'] = $request->query->get('vars')['status'];
        // 1代表当天
        $vars['dateType'] = $request->query->get('vars')['dateType'] ?: 1;

        if ($vars['dateType'] == 1) {
            $vars['startDate'] = date('Y-m-d');
            $vars['endDate'] = date('Y-m-d');
        } else {
            $vars['startDate'] = $request->query->get('vars')['startDate'];
            $vars['endDate'] = $request->query->get('vars')['endDate'];
        }

        if ($this->isGranted('ROLE_EXAMER')) {
            $companyName = $vars['company'];
        } else {
            // 拥有ROLE_EXAMER_HPL角色只能看到和自己公司名称一样的记录
            $companyName = $this->getUser()->getAgencyRels()[0]->getCompany()->getCompany();
        }

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findOrderChecked($vars['mixed'], $vars['status'], $vars['startDate'], $vars['endDate'], $companyName, true);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/checked_list.html.twig', array(
            'title' => $title,
            'companyRepo' => $companyRepo,
            'vars'  => $vars,
            'bf' => $bf,
            'vars' => $vars,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
            'showExamer' => $showExamer,
            'showMaintain' => $showMaintain,
            'showReport' => $showReport,
        ));
    }

    /**
     * 从远程监测转到又一车erp的逻辑
     * @Security("has_role('ROLE_EXAMER')")
     * @Route("/passToErp/{id}",name="order_passToRrp")
     */
    public function passToErpAction(Order $order, Request $request)
    {
        return new JsonResponse(array('success' => true, 'msg' => '已转成功！'));
    }

    /**
     * 复审师审核报告
     * @Security("has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/confirm", name="order_confirm")
     */
    public function confirmListAction(Request $request)
    {
        $title = '复审列表';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;
        $vars['dateType'] = $request->query->get('vars')['dateType'];
        $vars['startDate'] = $request->query->get('vars')['startDate'];
        $vars['endDate'] = $request->query->get('vars')['endDate'];
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        if($this->isGranted('ROLE_EXAMER_MANAGER')) {
            $user = null;
        }

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findConfirmOrder($vars['startDate'], $vars['endDate'], $user);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/confirm_list.html.twig', array(
            'title' => $title,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
            'vars' => $vars,
        ));
    }

    /**
     * 复审师审核结果
     * @Security("has_role('ROLE_EXAMER_RECHECK')")
     * @Route("/result_confirm", name="order_result_confirm")
     */
    public function confirmResultAction(Request $request)
    {
        $title = '复审结果';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;
        // 1代表当天
        $vars['dateType'] = $request->query->get('vars')['dateType'] ?: 1;

        if ($vars['dateType'] == 1) {
            $vars['startDate'] = date('Y-m-d');
            $vars['endDate'] = date('Y-m-d');
        } else {
            $vars['startDate'] = $request->query->get('vars')['startDate'];
            $vars['endDate'] = $request->query->get('vars')['endDate'];
        }

        $user = $this->getUser();
        if($this->isGranted('ROLE_ADMIN')) {
            $user = null;
        }

        $query = $this->getDoctrine()->getRepository('AppBundle:Order')
            ->findConfirmResultOrder($vars['startDate'], $vars['endDate'], $user);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/result_confirm.html.twig', array(
            'title' => $title,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
            'vars' => $vars,
        ));
    }

    /**
     * 查所有订单
     * @Security("has_role('ROLE_LOADOFFICER_MANAGER') or has_role('ROLE_EXAMER_MANAGER') or has_role('ROLE_ADMIN_HPL')")
     * @Route("/all", name="order_all")
     */
    public function allAction(Request $request)
    {
        $title = '订单查询';
        $company = $this->getUser()->getAgencyRels()[0]->getCompany()->getCompany();
        $showExamer = $this->get('app.business_factory')->getFieldPolicy($company)['showExamer'];
        $showReport = $this->get('app.business_factory')->getFieldPolicy($company)['report'];
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $vars['mixed'] = $request->query->get('vars')['mixed'];
        $id = $request->query->get('id',null);
        $companys = [];
        $agencyIds = [];
        if ($this->isGranted('ROLE_EXAMER_MANAGER')) {
        } elseif ($this->isGranted('ROLE_LOADOFFICER_MANAGER')) {
            $agencyRels = $this->getUser()->getAgencyRels();
            foreach ($agencyRels as $agencyRel) {
                $agencyIds[] = $agencyRel->getAgency()->getId();
            }
        } else {
            $agencyRels = $this->getUser()->getAgencyRels();
            foreach ($agencyRels as $agencyRel) {
                $companys[] = $agencyRel->getCompany();
            }
        }

        if ($vars['mixed'] || $id) {
            $query = $this->getDoctrine()->getRepository('AppBundle:Order')
                ->findAllOrder($vars['mixed'], $id, $companys, $agencyIds, true);
        } else {
            $query = [];
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit/*limit per page*/
        );

        return $this->render('order/all_list.html.twig', array(
            'title' => $title,
            'vars'  => $vars,
            'id' => $id,
            'showExamer' => $showExamer,
            'showReport' => $showReport,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
        ));
    }

    /***********ajax api**************************/

    /**
     * 发送通知到第三方公司
     * @Security("has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/notify", name="order_notify")
     * @Method("post")
     */
    public function notify(Request $request)
    {
        $orderNo = $request->request->get('orderNo');
        $this->get("util.rabbitmq")->sendCompanyNotify($orderNo);

        return JsonResponse::create(["success" => true, "msg" => "$orderNo 已通知，最长三分钟后刷新页面可以看到通知结果"]);
    }

    /**
     * @Route("/update/{id}",name="order_update")
     * @Method("post")
     * @ParamConverter("order", class="AppBundle:Order")
     */
    public function updateOrderAction(Order $order, Request $request)
    {
        $this->checkOrderOwner($order, Order::STATUS_EDIT);
        $posts = $request->request->all();
        $orderLogic = $this->get("OrderLogic");
        $orderLogic->updateOrder($order, $posts);
        return JsonResponse::create(["sucess" => true, "ret" => $order->getId()]);
    }

    /**
     * 修改预售价格
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/updateValuation/{id}",name="order_update_valuation")
     * @Method("post")
     */
    public function updateValuationAction(Order $order, Request $request)
    {
        $valuation = $request->request->get('price');
        if ($valuation) {
            $order->setValuationLog('原评估单号:' . $order->getOrderNo() .'原预售价格：'.$order->getValuation().'新预售价格:'.$valuation. ',操作者id:' . $this->getUser()->getId() . ',操作者姓名:' . $this->getUser()->getName() . ',操作时间:' . date('Y-m-d H:i:s'));
            $order->setValuation($valuation);
            $this->getDoctrine()->getManager()->flush();

            return JsonResponse::create(["success" => true, "price" => $order->getValuation()]);
        } else {
            return JsonResponse::create(["success" => false, 'msg' => '预售价格不能为空']);
        }
    }

    /***********private 公用函数*****************/
    private function checkOrderOwner(Order $order, $status)
    {
        if ($order->getStatus() !== $status) {
            throw $this->createAccessDeniedException('检测订单状态不对！');
        }
        if ($status === Order::STATUS_EDIT && $order->getLoadOfficer() !== $this->getUser()) {
            throw $this->createAccessDeniedException('这不是你的检测订单！');
        }
    }
}
