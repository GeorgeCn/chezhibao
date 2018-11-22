<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserParameterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('k1', null, array(
                'label' => '车主的ID',
                'constraints' => new NotBlank(array('groups' => array('Kefu'))),
            ))
            ->add('k2', null, array(
                'label' => '我的ID',
                'constraints' => new NotBlank(array('groups' => array('Kefu'))),
                ))
        ;
    }
}
