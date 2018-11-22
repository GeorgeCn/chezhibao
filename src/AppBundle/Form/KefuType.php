<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class KefuType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('label' => '登录账号'))
            ->add('plainPassword', null, array('label' => '密码', 'data' => substr(str_shuffle("01234567890123456789"), 0, 6)))
            ->add('name', null, array('label' => '姓名'))
            ->add('company', 'hidden', array('label' => '公司名字', 'data' => '客服创建'))
            ->add('companyCode', 'hidden', array('label' => '公司代码', 'data' => 'kf'))
            ->add('mobile', "text", array('label' => '手机号码'))
            // ->add('email', null, array('label' => '邮箱'))
            // 省份和城市字段隐藏，默认值由控制器里面去初始化
            ->add('province', EntityType::class, array(
                'class' => 'AppBundle:Province',
                'placeholder' => '',
                'choice_label' => 'name',
                'label' => false,
                'attr' => array('class' => 'hidden'),
            ))
            ->add('city', EntityType::class, array(
                'class' => 'AppBundle:City',
                'placeholder' => '',
                'choice_label' => 'name',
                'label' => false,
                'attr' => array('class' => 'hidden'),
            ))
            ->add('data', UserParameterType::class, array('label' => '用户的相关数据'))
            ->add('enabled', 'hidden', array('label' => '账号是否启用'))
            ->add('type', 'hidden', array('data' => 4))
            ->add('roles', 'collection', array(
                'data' => array('ROLE_LOADOFFICER'),
                'label' => false,
                'entry_type'   => 'hidden',
            ))
        ;
    }

    public function getBlockPrefix()
    {
        return 'app_kefu_info';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            'attr'=>array('novalidate'=>'novalidate'),
            'validation_groups' => array('Kefu'),
        ));
    }
}

