<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Province;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, array('label' => '登录账号'))
            ->add('plainPassword', null, array('label' => '密码'))
            ->add('name', null, array('label' => '姓名'))
            ->add('mobile', "text", array('label' => '手机号码'))
            ->add('province', EntityType::class, array(
                'class' => 'AppBundle:Province',
                'placeholder' => '',
                'choice_label' => 'name',
                'label' => '省份',
                'constraints' => new NotBlank(array('message' => "省份不能为空")),
            ))
            // reserve position(保留占用位置，否则通过事件加的这个字段会排列到最后)
            ->add('city', 'hidden')
            ->add('enabled', null, array('label' => '账号是否启用'))
        ;

        if ($this->hasRoleAdmin()) {
            $builder
                ->add('receive_owner', CheckboxType::class, array('label' => '是否接收本人订单审核通过的短信'))
                ->add('receive_lower', CheckboxType::class, array('label' => '是否接收下级订单状态变更的短信'))
                ->add('receive_types', ChoiceType::class, array(
                    'choices' => array(
                        User::RECEIVE_TYPE_SUBMIT => '当订单提交时',
                        User::RECEIVE_TYPE_REPORT_PASS => '当订单被又一车审核通过时',
                        User::RECEIVE_TYPE_REPORT_FAIL => '当订单被又一车审核失败时',
                    ),
                    'label' => '接收类型',
                    'expanded' => true,
                    'multiple' => true,
                    'mapped' => true,
                ))
                ->add('examerReceive', null, array('label' => '复核师是否接收订单预警短信（仅对复核师角色生效）'))
                ->add('roles', 'choice', array(
                    'choices' => $this->getExistingRoles(),
                    'data' => $options['data']->getRoles(),
                    'label' => '角色',
                    'expanded' => true,
                    'multiple' => true,
                    'mapped' => true,
                ))
            ;
        } else {
            // 如果是ROLE_LOADOFFICER_MANAGER的角色，下面的字段在新增和编辑时都隐藏，值和当前该用户的值一样
            $builder
                ->add('roles', 'collection', array(
                    'data' => array('ROLE_LOADOFFICER'),
                    'label' => false,
                    'entry_type' => 'hidden',
            ));
        }

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
                'constraints' => new NotBlank(array('message' => "城市不能为空")),
            ));
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getProvince());
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

    // public function getBlockPrefix()
    // {
    //     return 'app_user_info';
    // }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\User',
            'attr'=>array('novalidate'=>'novalidate'),
            'validation_groups' => array('Custom', 'CustomNew'),
        ));
    }

    /**
     * 获取系统已定义的角色
     */
    public function getExistingRoles()
    {
        $roleHierarchy = $this->container->getParameter('security.role_hierarchy.roles');
        $roles = array_keys($roleHierarchy);

        $rolesMap = $this->getRolesMap();

        foreach ($roles as $role) {
            if (isset($rolesMap[$role])) {
                $resultRoles[$role] = $role.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$rolesMap[$role];
            } else {
                $resultRoles[$role] = $role;
            }
        }

        return $resultRoles;
    }

    /**
     * 获取角色对应的中文名
     */
    public function getRolesMap()
    {
        $rolesMap = array(
            'ROLE_LOADOFFICER' => '采集员',
            'ROLE_LOADOFFICER_MANAGER' => '采集员主管',
            'ROLE_EXAMER' => '复核师-又一车',
            'ROLE_EXAMER_RECHECK' => '复审专员-又一车',
            'ROLE_EXAMER_MANAGER' => '复核师-又一车主管',
            'ROLE_EXAMER_HPL' => '复核师-第三方',
            'ROLE_ADMIN_HPL' => '复核师主管-第三方',
            'ROLE_ADMIN' => '系统管理员',
            'ROLE_KEFU' => '客服',
        );

        return $rolesMap;
    }

    /**
     * 检查用户是否有ROLE_ADMIN的权限,用来显示不同的form
     */
    public function hasRoleAdmin()
    {
        $ac = $this->container->get('security.authorization_checker');

        return $ac->isGranted('ROLE_ADMIN');
    }

    /**
     * 检查用户是否有ROLE_LOADOFFICER_MANAGER的权限,用来显示不同的form
     */
    public function hasRoleLoadOfficerManager()
    {
        $ac = $this->container->get('security.authorization_checker');

        return $ac->isGranted('ROLE_LOADOFFICER_MANAGER');
    }

    /**
     * 检查用户是否有ROLE_EXAMER_HPL的权限,用来显示不同的form
     */
    public function hasRoleExamerHpl()
    {
        $ac = $this->container->get('security.authorization_checker');

        return $ac->isGranted('ROLE_EXAMER_HPL');
    }
}

