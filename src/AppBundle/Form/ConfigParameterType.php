<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * 公司配置表中的对接对方公司字段
 */
class ConfigParameterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('k1', null, array('label' => 'url'))
            ->add('k2', null, array('label' => '其它参数1'))
            ->add('k3', null, array('label' => '其它参数2'))
            ->add('enabled', 'choice', array(
                'choices' => array(
                    'true' => '是否启用通知'
                ),
                'label' => false,
                'expanded' => true,
                'multiple' => true,
                'mapped' => true,
            ))
        ;
    }
}
