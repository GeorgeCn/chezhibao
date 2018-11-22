<?php
// src/AppBundle/Menu/Builder.php
namespace AppBundle\Menu;

use AppBundle\Traits\ContainerAwareTrait;
use Knp\Menu\FactoryInterface;
// use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\ExpressionLanguage\Expression;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        // 给根ul标签加id和class
        $menu = $factory->createItem('root', array(
            'childrenAttributes' => array(
                'id'    => 'sideMenu',
                'class' => 'nav',
            ),
        ));

        $ac = $this->get('security.authorization_checker');

        // 部分公司菜单有隐藏
        $bf     = $this->get('app.business_factory');
        $fields = $bf->getFieldPolicy($this->getUser()->getAgencyRels()[0]->getCompany()->getCompany());

        if ($ac->isGranted(new Expression('has_role("ROLE_LOADOFFICER") or has_role("ROLE_LOADOFFICER_MANAGER") or has_role("ROLE_EXAMER_MANAGER") or has_role("ROLE_EXAMER_HPL") or has_role("ROLE_ADMIN_HPL")'))) {
            $menu->addChild('业务报表', array('uri' => '#', 'attributes' => array('i-class' => 'icons-menu icon-font-yewubaobiao')));
            //$menu['业务报表']->addChild('信贷员业绩', array('route' => 'loadofficer_report', 'attributes'));
            $menu['业务报表']->addChild('车辆详表', array('route' => 'vehicle_report', 'attributes'));
        }

        if ($ac->isGranted(new Expression('has_role("ROLE_LOADOFFICER_MANAGER") or has_role("ROLE_EXAMER_MANAGER") or has_role("ROLE_ADMIN_HPL")'))) {;
            $menu['业务报表']->addChild('订单查询', array('route' => 'order_all', 'attributes'));
        }

        if ($ac->isGranted(new Expression('has_role("ROLE_EXAMER") or has_role("ROLE_EXAMER_HPL")'))) {

            $menu->addChild('报告审核', array('uri' => '#', 'attributes' => array('i-class' => 'icons-menu icon-font-baogaofensheng')));
        }

        if ($ac->isGranted('ROLE_EXAMER_MANAGER')) {
            $menu['报告审核']->addChild('任务列表', array('route' => 'order_task_list'));
        }

        if ($ac->isGranted('ROLE_EXAMER')) {
            $menu['报告审核']->addChild('接单', array('route' => 'order_task'));
        }

        if ($ac->isGranted('ROLE_EXAMER_MANAGER')) {
            $menu['报告审核']->addChild('退单历史', array('route' => 'back_audit_list'));
        }

        if ($ac->isGranted(new Expression('has_role("ROLE_EXAMER") or has_role("ROLE_EXAMER_HPL")'))) {
            $menu['报告审核']->addChild('已审核', array('route' => 'order_checked_list', 'attributes'));
        }

        if ($ac->isGranted('ROLE_EXAMER')) {
            $menu['报告审核']->addChild('已退回', array('route' => 'order_examer_back_list', 'attributes'));
        }

        if ($ac->isGranted('ROLE_EXAMER_MANAGER')) {
            $menu['报告审核']->addChild('异常处理', array('route' => 'order_abnormal', 'attributes' => array('i-class' => 'icons-menu yccl')));
        }

        if ($ac->isGranted('ROLE_EXAMER_RECHECK') || ($ac->isGranted('ROLE_EXAMER_HPL') && $fields['fsMenu'])) {
            $menu->addChild('报告复审', array('uri' => '#', 'attributes' => array('i-class' => 'icons-menu icon-font-baogaoshenhe')));
        }

        if ($ac->isGranted('ROLE_EXAMER_MANAGER')) {
            $menu['报告复审']->addChild('复审列表', array('route' => 'order_confirm'));
        }

        if ($ac->isGranted('ROLE_EXAMER_RECHECK')) {
            $menu['报告复审']->addChild('复审接单', array('route' => 'order_getconfirm'));
        }

        if ($ac->isGranted('ROLE_EXAMER_RECHECK')) {
            $menu['报告复审']->addChild('复审结果', array('route' => 'order_result_confirm'));
            $menu['报告复审']->addChild('已退回', array('route' => 'order_examer_recheck_back_list', 'attributes'));
        }

        if ($ac->isGranted(new Expression('has_role("ROLE_EXAMER_HPL") or has_role("ROLE_EXAMER_MANAGER")'))) {
            // 如果是美利金融公司，下面的菜单隐藏
            if ($fields['fsMenu']) {
                // 获取订单不同状态的数量,如果有数量为0的渲染时不显示数量
                $count        = $this->get('OrderLogic')->countOrder($this->getUser()->getId());
                $recheckCount = $count['recheckCount'] ? $count['recheckCount'] : '';

                $menu['报告复审']->addChild('第三方复审', array('route' => 'order_recheck_list', 'attributes' => array('span-class' => 'badge badge-primary recheck-count', 'value' => $recheckCount)));
            }
        }

        if ($ac->isGranted(new Expression('has_role("ROLE_LOADOFFICER_MANAGER") or has_role("ROLE_EXAMER_HPL") or has_role("ROLE_ADMIN_HPL")'))) {
            $menu->addChild('用户管理', array('uri' => '#', 'attributes' => array('i-class' => 'icons-menu icon-font-yonghuguanli')));
        }

        if ($ac->isGranted('ROLE_ADMIN_HPL')) {
            $menu['用户管理']->addChild('经销商列表', array('route' => 'agency_index'));
        }

        if ($ac->isGranted(new Expression('has_role("ROLE_ADMIN_HPL") or has_role("ROLE_LOADOFFICER_MANAGER")'))) {
            $menu['用户管理']->addChild('授权申请列表', array('route' => 'user_apply'));
        }

        if ($ac->isGranted(new Expression('has_role("ROLE_ADMIN_HPL") or has_role("ROLE_LOADOFFICER_MANAGER") or has_role("ROLE_EXAMER_HPL")'))) {
            $menu['用户管理']->addChild('用户列表', array('route' => 'user_index', 'attributes'));
        }

        if ($ac->isGranted('ROLE_EXAMER_MANAGER')) {
            $menu->addChild('系统配置', array('uri' => '#', 'attributes' => array('i-class' => 'icons-menu icon-font-xitongpeizhi')));
            $menu['系统配置']->addChild('异常组配置', array('route' => 'user_edit_abnormal'));
            $menu['系统配置']->addChild('复审名单配置', array('route' => 'user_edit_noob'));
        }

        if ($ac->isGranted('ROLE_ADMIN')) {
            $menu['系统配置']->addChild('公司配置', array('route' => 'config_index', 'attributes'));
        }

        return $menu;
    }
}
