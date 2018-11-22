<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Form;

use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Province;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array(
                'label' => '用户名',
                'attr' => array(
                    'placeholder' => '请输入您的用户名',
                ),
            ))
            ->add('plainPassword', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\RepeatedType'), array(
                'type' => LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\PasswordType'),
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array(
                    'label' => 'form.password',
                    'attr' => array(
                        'placeholder' => '请输入字母、数字，8-16位',
                    ),
                ),
                'second_options' => array(
                    'label' => 'form.password_confirmation',
                    'attr' => array(
                        'placeholder' => '请确认密码',
                    ),
                ),
                'invalid_message' => 'fos_user.password.mismatch',
            ))
            ->add('name', null, array(
                'label' => '姓名',
                'attr' => array(
                    'placeholder' => '请输入真实姓名',
                ),
            ))
            ->add('company', null, array(
                'label' => '公司名',
                'attr' => array(
                    'placeholder' => '请输入您的公司完整名称',
                ),
            ))
            ->add('email', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\EmailType'), array(
                'label' => 'form.email', 'translation_domain' => 'FOSUserBundle',
                'attr' => array(
                    'placeholder' => '请输入您的联系邮箱"',
                ),
            ))
            ->add('province', EntityType::class, array(
                'class' => 'AppBundle:Province',
                'placeholder' => '',
                'choice_label' => 'name',
                'label' => '省份',
            ))
            ->add('mobile', 'hidden', array(
                'label' => '手机号码',
            ))
        ;

        // 下面的代码来自http://symfony.com/doc/2.8/form/dynamic_form_modification.html的
        // Dynamic Generation for Submitted Forms
        $formModifier = function (FormInterface $form, Province $province = null) {
            $cities = null === $province ? array() : $province->getCities();

            $form->add('city', EntityType::class, array(
                'class' => 'AppBundle:City',
                'placeholder' => '',
                'choices' => $cities,
                'choice_label' => 'name',
                'label' => '城市',
            ));
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity
                $data = $event->getData();

                if ($data) {
                    $formModifier($event->getForm(), $data->getProvince());
                }
            }
        );

        $builder->get('province')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $province = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $province);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            'csrf_token_id' => 'registration',
            // BC for SF < 2.8
            'intention'  => 'registration',
        ));
    }

    // BC for SF < 2.7
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    // BC for SF < 3.0
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }
}
