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
use AppBundle\Entity\Config;
use AppBundle\Entity\Agency;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Doctrine\ORM\EntityRepository;

class ApplyType extends AbstractType
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
                ->add('grade', 'choice' , array('label' => '账号等级', 'choices' =>[0=>null, 1=>'高', 2=>'中', 3=>'低'], 'expanded' => false, 'multiple' => false, 'required'=>true))
                ->add('mobile', null, array('label'=>'手机号码'));
            if ($this->hasRoleLoadOfficerManager()) {
                $company = $this->findUserCompanies();
                global$companies;
                $companies = array_column($company,'id');
                $builder
                        ->add('company', EntityType::class, array(
                                'class' => 'AppBundle:Config',
                                'placeholder' => '',
                                'query_builder' => function (EntityRepository $er) {
                                    return $er->createQueryBuilder('c')
                                        ->where("c.id in (:companies)")
                                        ->setParameter('companies', $GLOBALS['companies'])
                                    ;
                                },
                                'choice_label' => 'name',
                                'label' => '金融公司',
                                'constraints' => new NotBlank(array('message' => "金融公司不能为空")),
                            ))
                        ->add('agency', 'hidden');
            } else {
                $builder
                ->add('company', EntityType::class, array(
                                'class' => 'AppBundle:Config',
                                'placeholder' => '',
                                'choice_label' => 'name',
                                'label' => '金融公司',
                                'constraints' => new NotBlank(array('message' => "金融公司不能为空")),
                            ))
                ->add('agency', 'hidden');
            }
        $builder
                ->add('province', EntityType::class, array(
                    'class' => 'AppBundle:Province',
                    'placeholder' => '',
                    'choice_label' => 'name',
                    'label' => '省份',
                    'constraints' => new NotBlank(array('message' => "省份不能为空")),
                ))
                ->add('city', 'hidden');

        // 下面的代码来自http://symfony.com/doc/2.8/form/dynamic_form_modification.html的
        // Dynamic Generation for Submitted Forms
        $formModifier = function (FormInterface $form, Config $company = null) {

            if ($this->hasRoleAdmin()) {
                $agencies = null === $company ? array() : $company->getAgencies();
                $form->add('agency', EntityType::class, array(
                    'class' => 'AppBundle:Agency',
                    'placeholder' => '',
                    'choices' => $agencies,
                    'choice_label' => 'name',
                    'label' => '经销商',
                    'constraints' => new NotBlank(array('message' => "经销商不能为空")),
                ));      
            } elseif ($this->hasRoleAdminHpl()) {
                global $companyId;
                $companyId = null === $company ? 0 : $company->getId(); 
                $form->add('agency', EntityType::class, array(
                    'class' => 'AppBundle:Agency',
                    'placeholder' => '',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('a')
                            ->where("a.company = :companyId and a.code != 'admin'")
                            ->setParameter('companyId', $GLOBALS['companyId'])
                        ;
                    },
                    'choice_label' => 'name',
                    'label' => '经销商',
                    'constraints' => new NotBlank(array('message' => "经销商不能为空")),
                ));   
            } else {
                global $companyId,$agencyId;
                $companyId = null === $company ? 0 : $company->getId(); 
                $agency = $this->findUserAgency($company);
                $agencyId = array_column($agency,'id');
                $form->add('agency', EntityType::class, array(
                    'class' => 'AppBundle:Agency',
                    'placeholder' => '',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('a')
                            ->where("a.id in (:agencyId)")
                            ->setParameter('agencyId', $GLOBALS['agencyId'])
                        ;
                    },
                    'choice_label' => 'name',
                    'label' => '经销商',
                    'constraints' => new NotBlank(array('message' => "经销商不能为空")),
                )); 
            } 
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getCompany());
            }
        );

        $builder->get('company')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $company = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $company);
            }
        );

        $formModifiers = function (FormInterface $form, Province $province = null) {
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
            function (FormEvent $event) use ($formModifiers) {
                // this would be your entity
                $data = $event->getData();

                $formModifiers($event->getForm(), $data->getProvince());
            }
        );

        $builder->get('province')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifiers) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $province = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifiers($event->getForm()->getParent(), $province);
            }
        );
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
     * 检查用户是否有ROLE_ADMIN_HPL的权限,用来显示不同的form
     */
    public function hasRoleAdminHpl()
    {
        $ac = $this->container->get('security.authorization_checker');

        return $ac->isGranted('ROLE_ADMIN_HPL');
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
     * 查找当前用户所属金融公司
     */
    public function findUserCompanies()
    {
        $ac = $this->container->get('ApplyLogic');

        return $ac->findUserCompines();
    }

    /**
     * 查找指定金融公司的经销商
     */
    public function findUserAgency($company)
    {
        $ac = $this->container->get('ApplyLogic');

        return $ac->findUserAgency($company);
    }
}

