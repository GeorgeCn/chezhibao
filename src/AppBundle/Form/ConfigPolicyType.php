<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * 公司配置表中的控制字段显示策略字段
 */
class ConfigPolicyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('show', 'choice', array(
                'choices' => array(
                    'valuation' => '预售/预估价格',
                    'businessNumber' => '业务流水号',
                    'reportLoadofficer' => '评估报告（ROLE_LOADOFFICER采集员角色）',
                    'maintainLoadofficer' => '维修记录（ROLE_LOADOFFICER采集员角色）',
                    'reportLmanager' => '评估报告（ROLE_LOADOFFICER_MANAGER采集员主管）',
                    'maintainLmanager' => '维修记录（ROLE_LOADOFFICER_MANAGER采集员主管）',
                    'reportEhpl' => '评估报告（ROLE_EXAMER_HPL复核师-第三方）',
                    'maintainEhpl' => '维修记录（ROLE_EXAMER_HPL复核师-第三方）',
                    'fsMenu' => '复审菜单',
                    'reportPrice' => '评估报告中车辆价格影响因素',
                    'reportPriceTrend' => '评估报告中车辆未来的价格走势',
                    'purchasePricePc' => '收购价（pc端）',
                    'sellPricePc' => '销售价（pc端）',
                    'futurePricePc' => '未来价格（pc端）',
                    'purchasePriceApp' => '收购价（app端）',
                    'sellPriceApp' => '销售价（app端）',
                    'futurePriceApp' => '未来价格（app端）',
                ),
                'label' => '字段是否显示',
                'expanded' => true,
                'multiple' => true,
            ))
        ;
    }
}
