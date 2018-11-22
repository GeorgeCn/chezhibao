<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * 公司配置表各字段
 */
class ConfigType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', null, array('label' => '总部名称'))
            ->add('companyKey', null, 
                [
                    'label' => '签名key',
                    'attr' => [
                        'readonly' => 'readonly',
                        'placeholder' => '系统自动生成key'
                    ]
                ]
                )
            ->add('companySerect', null,
                [
                    'label' => '签名serect',
                    'attr' => [
                        'readonly' => 'readonly',
                        'placeholder' => '系统自动生成sercet'
                    ]
                ]
                )
            ->add('companyNew', null, array('label' => '新打单公司'))
            ->add('needRecheck', null, array('label' => '是否要复审'))
            ->add('needVideo', null, array('label' => '是否要视频'))
            ->add('jytj', ConfigJytjType::class, array('label' => '审核要求'))
            ->add('parameter', ConfigParameterType::class, array('label' => '配置参数'))
            ->add('policy', ConfigPolicyType::class, array('label' => '字段策略控制'))
            ->add('info', ConfigInfoType::class, array('label' => '基本信息'))
            ->add('timeLimit', null, array(
                'label' => '短信提醒间隔',
                'attr' => array('placeholder' => '单位分钟')
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Config',
            'attr'=>array('novalidate'=>'novalidate'),
        ));
    }
}
