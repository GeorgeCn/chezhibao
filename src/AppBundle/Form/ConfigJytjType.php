<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

/**
 * 公司配置表中的审核要求字段雨（原经营条件字段）
 */
class ConfigJytjType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('k7', null, array(
                'label' => '订单审核时限',
                'attr' => array('placeholder' => '单位：分'),
                'constraints' => new Range(array('min' => 0, 'max' => 10080, 'maxMessage' => "最大值不能高于{{ limit }}分钟"))
            ))
            ->add('k6', 'textarea' , array('label' => '审核标准', 'constraints' => new NotBlank()))
        ;
    }
}
