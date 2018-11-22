<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\User;
use AppBundle\Entity\UserLog;
use AppBundle\Entity\Apply;
use AppBundle\Entity\AgencyRel;
use AppBundle\Entity\Config;
use AppBundle\Entity\Agency;
use AppBundle\Entity\Province;
use AppBundle\Entity\City;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormError;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * User controller.
 */
class UserController extends Controller
{
    /**
     * 拥有非ROLE_ADMIN角色的只能操作和他companyCode一样的人员(不包括他本身).
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ADMIN_HPL') or has_role('ROLE_LOADOFFICER_MANAGER') or has_role('ROLE_EXAMER_HPL')")
     * @Route("/user/", name="user_index")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $title = '用户列表';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $vars['name'] = $request->query->get('vars')['name'];
        $vars['mobile'] = $request->query->get('vars')['mobile'];
        $vars['username'] = $request->query->get('vars')['username'];
        $vars['company'] = $request->query->get('vars')['company'];
        $vars['agency'] = $request->query->get('vars')['agency'];
        $roles = null;
        $data = [];
        $em = $this->getDoctrine()->getManager();

        if ($this->isGranted('ROLE_ADMIN')) {
            $userId = null;
            $agencyId = $companyId = null;
            if(!empty($vars['company'])) {
                $company = $em->getRepository('AppBundle:Config')->findOneBy(['company' => $vars['company']]);
                $companyId = $company->getId();
            }
            if(!empty($vars['agency'])) {
                $agency = $em->getRepository('AppBundle:Agency')->findOneBy(['name' => $vars['agency']]);
                $agencyId = $agency->getId();
            }
        } elseif ($this->getUser()->hasRole('ROLE_ADMIN_HPL') || $this->getUser()->hasRole('ROLE_EXAMER_HPL')) {
            $userId = $this->getUser()->getId();
            $agencyId = null;
            $companyId = $this->getUser()->getAgencyRels()[0]->getCompany()->getId();
            if(!empty($vars['agency'])) {
                $agency = $em->getRepository('AppBundle:Agency')->findOneBy(['name' => $vars['agency']]);
                $agencyId = $agency->getId();
            }
            $roles = array(
                'a:1:{i:0;s:16:"ROLE_LOADOFFICER";}',
                'a:1:{i:0;s:24:"ROLE_LOADOFFICER_MANAGER";}',
            );
        } else {
            $agencyRel = $this->getUser()->getAgencyRels();
            $agencyId = [];
            foreach($agencyRel as $value) {
                if($value->getGrade() == 3) continue;
                $agencyId[] = $value->getAgency()->getId();
            }
            $companyId = null;
            $userId = $this->getUser()->getId();
            $roles = array(
                'a:1:{i:0;s:16:"ROLE_LOADOFFICER";}',
            );
        }

        $query = $this->getDoctrine()->getRepository('AppBundle:AgencyRel')
            ->findUser($userId, $agencyId, $vars['name'], $vars['mobile'], $vars['username'], $companyId, $roles);
        ;

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit /*limit per page */
        );

        return $this->render('user/index.html.twig', array(
            'title' => $title,
            'vars' => $vars,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
        ));
    }

    /**
     * Creates a new User entity.
     * 拥有非ROLE_ADMIN角色的只能操作和他companyCode一样的人员(不包括他本身).
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/user/new", name="user_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $companies = $em->getRepository('AppBundle:Config')->findCompanyNamesAndId();
        $companies = array_column($companies, 'company', 'id');
        $validationGroups = array('Custom', 'CustomNew');
        $form = $this->createFormBuilder($user, array("validation_groups" => $validationGroups))
              ->add('username', null, array('label' => '登录账号'))
              ->add('plainPassword', null, array('label' => '密码'))
              ->add('name', null, array('label' => '姓名'))
              ->add('mobile', "text", array('label' => '手机号码'))
              ->add('company', 'choice' , array('label' => '金融公司', 'choices'=>$companies, 'mapped'=>false))
              ->add('roles', 'choice', array(
                    'choices' =>["ROLE_EXAMER" => "ROLE_EXAMER&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;审核师-又一车", "ROLE_EXAMER_RECHECK" => "ROLE_EXAMER_RECHECK&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;复核师专员-又一车", "ROLE_EXAMER_MANAGER" => "ROLE_EXAMER_MANAGER&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;复核师-又一车主管", "ROLE_EXAMER_HPL" => "ROLE_EXAMER_HPL&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;复核师-第三方"],
                    'label' => '角色',
                    'expanded' => true,
                    'multiple' => true,
                    'mapped' => true,
                ))
              ->getForm();                  

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $role = $request->request->get('form')['roles'][0];
            $companyId = $request->request->get('form')['company'];
            $creater = $this->getUser();
            $province = $em->getRepository("AppBundle:Province")->findOneBy(['name'=>'上海市']);
            $city = $em->getRepository("AppBundle:City")->findOneBy(['name'=>'上海市']);
            if($role == 'ROLE_EXAMER_HPL' && !empty($companyId)) {
                $company = $em->getRepository("AppBundle:Config")->find($companyId);
                $agency = $em->getRepository("AppBundle:Agency")->findOneBy(['company'=>$company, 'code'=>'admin']); 
            } else {
                $company = $em->getRepository("AppBundle:Config")->findOneBy(['company'=>'又一车']);
                $agency = $em->getRepository("AppBundle:Agency")->findOneBy(['company'=>$company, 'code'=>'admin']); 
            }
            $user->setCreater($creater);
            $user->setProvince($province);
            $user->setCity($city);
            $em->persist($user);
            $em->flush();

            $agencyRel = new AgencyRel();
            $agencyRel->setCreater($this->getUser());
            $agencyRel->setCreatedAt(new \DateTime());
            $agencyRel->setGrade(0);
            $agencyRel->setUser($user);
            $agencyRel->setCompany($company);
            $agencyRel->setAgency($agency);
            $em->persist($agencyRel);
            $em->flush();

            $this->addFlash(
                'notice',
                '创建成功'
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     * 拥有非ROLE_ADMIN角色的只能操作和他companyCode一样的人员(不包括他本身).
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/user/{id}/edit", name="user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, User $user)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->checkOwner($user);
        }

        // 编辑模式下用Custom(对密码不做验证)和CustomEdit验证组
        $validationGroups = array('Custom', 'CustomEdit');
        $editForm = $this->createForm('AppBundle\Form\UserType', $user, array("validation_groups" => $validationGroups));
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            if($user->isEnabled() == false) {
                $em = $this->getDoctrine()->getManager();
                $agencyRel = $em->getRepository('AppBundle:AgencyRel')->findOneBy(['user'=>$user->getId()]);
                $em->remove($agencyRel);
            }
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/edit_abnoramal", name="user_edit_abnormal")
     * @Method({"GET", "POST"})
     */
    public function editAbnormalAction(Request $request)
    {
        $editForm = $this->createFormBuilder()
            ->add('abnormal', EntityType::class, array(
                'class' => 'AppBundle:User',
                // 只获取有审核师角色的列表
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roleType = :roleType and u.enabled = true')
                        ->setParameter('roleType', User::TYPE_EXAMER)
                    ;
                },
                'multiple' => true,
                'choice_label' => 'name',
                'label' => false,
                    'attr' => array(
                        'class' => 'select2',
                    ),
                'data' => $this->getDoctrine()->getRepository('AppBundle:User')->findBy(['abnormal' => 1, 'roleType' => User::TYPE_EXAMER, 'enabled' => true]),
             ))
            ->getForm()
        ;

        $em = $this->getDoctrine()->getManager();
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //将非选中的用户更新为非异常
            $q = $em->createQuery('update AppBundle:user u set u.abnormal = 0 where u not in (:users)')
                ->setParameter('users', $editForm->getData()['abnormal']);
            $q->execute();
            //将选中的用户更新为异常
            $q = $em->createQuery('update AppBundle:user u set u.abnormal = 1 where u in (:users)')
                ->setParameter('users', $editForm->getData()['abnormal']);
            $q->execute();

            $this->addFlash(
                'notice',
                '保存成功'
            );
        }

        return $this->render('user/edit_abnormal.html.twig', array(
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * @Security("has_role('ROLE_EXAMER_MANAGER')")
     * @Route("/edit_noob", name="user_edit_noob")
     * @Method({"GET", "POST"})
     */
    public function editNoobAction(Request $request)
    {
        $editForm = $this->createFormBuilder(null, ['attr'=>['novalidate'=>'novalidate']])
            ->add('noob', EntityType::class, array(
                'class' => 'AppBundle:User',
                // 只获取有审核师角色的列表
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roleType = :roleType and u.enabled = true')
                        ->setParameter('roleType', User::TYPE_EXAMER)
                    ;
                },
                'multiple' => true,
                'choice_label' => 'name',
                'label' => '需复审人员',
                    'attr' => array(
                        'class' => 'select2 custom1',
                    ),
                'data' => $this->getDoctrine()->getRepository('AppBundle:User')->findBy(['noob' => 1, 'roleType' => User::TYPE_EXAMER, 'enabled' => true]),
             ))
            ->add('noobAllowed', EntityType::class, array(
                'class' => 'AppBundle:Config',
                // 只获取允许新人审核的公司
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                    ;
                },
                'multiple' => true,
                'choice_label' => 'name',
                'label' => '不允许审核的公司',
                    'attr' => array(
                        'class' => 'select2',
                    ),
                'data' => $this->getDoctrine()->getRepository('AppBundle:Config')->findBy(['noobAllowed' => true]),
             ))
            ->getForm()
        ;

        $em = $this->getDoctrine()->getManager();
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            //将非选中的用户更新为非异常
            $q = $em->createQuery('update AppBundle:user u set u.noob = 0 where u not in (:users)')
                ->setParameter('users', $editForm->getData()['noob']);
            $q->execute();
            //将选中的用户更新为异常
            $q = $em->createQuery('update AppBundle:user u set u.noob = 1 where u in (:users)')
                ->setParameter('users', $editForm->getData()['noob']);
            $q->execute();

            //将非选中的公司更新为不允许新人审核
            $q2 = $em->createQuery('update AppBundle:Config c set c.noobAllowed = 0 where c not in (:companies)')
                ->setParameter('companies', $editForm->getData()['noobAllowed']);
            $q2->execute();
            //将选中的公司更新为允许新人审核
            $q2 = $em->createQuery('update AppBundle:Config c set c.noobAllowed = 1 where c in (:companies)')
                ->setParameter('companies', $editForm->getData()['noobAllowed']);
            $q2->execute();

            $this->addFlash(
                'notice',
                '保存成功'
            );
        }

        return $this->render('user/edit_noob.html.twig', array(
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a User entity.不通过form,直接删除
     * 拥有非ROLE_ADMIN角色的只能操作和他companyCode一样的人员(不包括他本身).
     * @Security("has_role('ROLE_ADMIN')")
     * @Route("/{id}/delete", name="user_delete")
     * 
     */
    public function deleteAction(Request $request, User $user)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->checkOwner($user);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        $this->addFlash(
            'notice',
            '删除成功'
        );

        return $this->redirectToRoute('user_index');
    }

    /**
     * 检查ROLE_LOADOFFICER_MANAGER角色是否操作的是自己范围的用户（编辑和删除时会检查）
     */
    private function checkOwner(User $user)
    {
        $companyName = $this->getUser()->getAgencyRels()[0]->getCompany()->getCompany();

        if ($companyName != $user->getAgencyRels()[0]->getCompany()->getCompany()) {
            throw $this->createAccessDeniedException('你无权限操作该用户！');
        }
    }

    /**
     * ajax更改用户上班状态
     * @Route("/edit_job", name="switch_job_status")
     */
    public function modifyJob(Request $request)
    {
        $user = $this->getUser();
        $status = $user->getIsJob();
        $user->setIsJob(!$status);

        $userID = $user->getId();
        $createAt = new \DateTime();
        $userLog = new UserLog();
        $userLog->setUserId($userID);
        $userLog->setJobStatus($status);
        $userLog->setCreatedAt($createAt);
    
        $em = $this->getDoctrine()->getManager();
                
        $em->persist($userLog);
        $em->flush();
        return new JsonResponse(["status"=>200,"message"=>"修改成功!","jobStatus"=>$user->getIsJob()]);
    } 

    /**
     * 用户禁用
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_LOADOFFICER_MANAGER') or has_role('ROLE_ADMIN_HPL')")
     * @Route("/forbidden/{id}", name="user_forbidden")
     */
    public function userForbiddenAction(AgencyRel $agencyRel)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($agencyRel);
        $em->flush();
        //授权解除，角色降级
        $grade = 3;
        $gradeArr = $arr = $data =[];
        $user = $agencyRel->getUser();
        $gradeArr = $em->getRepository('AppBundle:agencyRel')->findAgencyRelsGrade($user);
        if(!empty($gradeArr)) {
            foreach ($gradeArr as $value) {
                $arr[] = $value['grade'];
            }
            array_push($arr,$grade);
            $grade = min($arr);     
        }
        switch ($grade) {
            case 2:
                $data['role'] = 'ROLE_LOADOFFICER_MANAGER';
                break;
            case 3:
                $data['role'] = 'ROLE_LOADOFFICER';
                break;
            default:
                $data['role'] = 'ROLE_LOADOFFICER';
                break;
            }
        $user->setRoles(array('roles'=>$data['role']));
        $em->flush();
        $this->addFlash(
            'notice',
            '禁用成功'
        );

        return $this->redirectToRoute('user_index');
    }  

    /**
     * 授权申请列表
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_ADMIN_HPL') or has_role('ROLE_LOADOFFICER_MANAGER')")
     * @Route("/apply", name="user_apply")
     */
    public function applyAction(Request $request)
    {
        $title = '授权申请列表';
        $perPageLimit = $request->query->get('perPageLimit') ? $request->query->get('perPageLimit') : 20;

        $vars['mobile'] = $request->query->get('vars')['mobile'];
        $vars['applyStatus'] = $request->query->get('vars')['applyStatus'];
        $vars['dateType'] = $request->query->get('vars')['dateType'];
        $vars['startDate'] = $request->query->get('vars')['startDate'];
        $vars['endDate'] = $request->query->get('vars')['endDate'];
        $vars['company'] = $request->query->get('vars')['company'];
        $vars['agency'] = $request->query->get('vars')['agency'];
        $company = $agency = $grade = null;
        $creater = $this->getUser()->getUserName();
        $em = $this->getDoctrine()->getManager();

        if ($this->isGranted('ROLE_ADMIN')) {
            if(!empty($vars['company'])) {
                $company = $em->getRepository('AppBundle:Config')->findOneBy(['company' => $vars['company']]);
            } 
            if(!empty($vars['agency'])) {
                $agency = $em->getRepository('AppBundle:Agency')->findOneBy(['name' => $vars['agency']]);
            }
        } elseif ($this->getUser()->hasRole('ROLE_ADMIN_HPL')) {
            $company = $this->getUser()->getAgencyRels()[0]->getCompany();
            if(!empty($vars['agency'])) {
                $agency = $em->getRepository('AppBundle:Agency')->findOneBy(['name' => $vars['agency']]);
            }
            $grade = ['2', '3'];
        } else {
            if(empty($vars['company']) && empty($vars['agency']) ) {
                $agencyRel = $this->getUser()->getAgencyRels();
                $agency = [];
                foreach($agencyRel as $value) {
                    if($value->getGrade() == 3) continue;
                    $agency[] = $value->getAgency();
                }
            } elseif(!empty($vars['company']) && empty($vars['agency'])) {
                $agencyRel = $this->getUser()->getAgencyRels();
                $agency = [];
                foreach($agencyRel as $value) {
                    if($value->getGrade() == 3) continue;
                    $agency[] = $value->getAgency();
                }
            } else {
                $agency = $em->getRepository('AppBundle:Agency')->findOneBy(['name' => $vars['agency']]);
            }
            $grade = ['3'];
        }
        
        $query = $this->getDoctrine()->getRepository('AppBundle:Apply')
            ->findApply( $vars['applyStatus'], $vars['mobile'], $vars['startDate'], $vars['endDate'], $company, $agency, $grade);
        ;

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            $perPageLimit /*limit per page */
        );
        $userID = $this->getUser()->getId();
        $agencyRel = $em->getRepository("AppBundle:AgencyRel")->findOneBy(array('user'=>$userID));

        return $this->render('user/apply.html.twig', array(
            'title' => $title,
            'vars' => $vars,
            'pagination' => $pagination,
            'perPageLimit' => $perPageLimit,
            'applyLogic' => $this->get("ApplyLogic"),
        ));
    }

    /**
     * 新增授权申请
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_LOADOFFICER_MANAGER') or has_role('ROLE_ADMIN_HPL')")
     * @Route("/apply_new", name="user_apply_new")
     * @Method({"GET", "POST"})
     */
    public function applyNewAction(Request $request)
    {   
        $apply = new Apply();
        $form = $this->createForm('AppBundle\Form\ApplyType', $apply);
        $user = $this->getUser();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $grade = $apply->getGrade();
            $mobile = $apply->getMobile();
            $agencyRelUser = $em->getRepository("AppBundle:User")->findOneBy(array('mobile'=>$mobile, 'enabled'=>1));
            $agencyRel = $companyRel = null;
            if(!empty($agencyRelUser)) {
                if($grade == 1) {
                        $agencyRel = $em->getRepository("AppBundle:AgencyRel")->findBy(array('user'=>$agencyRelUser));
                        if(!empty($agencyRel)) {
                            $error = new FormError('该手机号码已绑定授权关系,请勿重复创建！');
                            $form->get('mobile')->addError($error);
                            return $this->render('user/apply_new.html.twig', array(
                                'user' => $user,
                                'form' => $form->createView(),
                            ));
                        }
                } elseif($grade == 2 || $grade == 3) {
                    $company = $apply->getCompany();
                    $agencyRel = $em->getRepository("AppBundle:AgencyRel")->findBy(array('user'=>$agencyRelUser, 'grade'=>1));
                    if(!empty($agencyRel)) {
                            $error = new FormError('该手机号码已绑定授权关系,请勿重复创建！');
                            $form->get('mobile')->addError($error);
                            return $this->render('user/apply_new.html.twig', array(
                                'user' => $user,
                                'form' => $form->createView(),
                            ));
                        }
                    $companyRel = $em->getRepository("AppBundle:AgencyRel")->findBy(array('user'=>$agencyRelUser, 'company'=>$company));
                    if(!empty($companyRel)) {
                            $error = new FormError('该手机号码已绑定授权关系,请勿重复创建！');
                            $form->get('mobile')->addError($error);
                            return $this->render('user/apply_new.html.twig', array(
                                'user' => $user,
                                'form' => $form->createView(),
                            ));
                        }
                }
            }
            //授权关系逻辑判断部分
            $apply_old = $em->getRepository('AppBundle:Apply')->findOneBy(array("mobile"=>$mobile, "status"=>0));
            if(!empty($apply_old)) {
                $apply_old->setStatus(2);//取消上一条授权
            }
            $rand = $this->get('util.random')->getRandomStr(6); 
            $creater = $this->getUser()->getName();
            $apply->setRand($rand);
            $apply->setCreatedAt(new \DateTime());
            $apply->setStatus(0);
            $apply->setCreater($creater);
            $em->persist($apply);
            $em->flush();
            $this->addFlash(
                'notice',
                '创建成功'
            );

            return $this->redirectToRoute('user_apply');
        }                 
 
        return $this->render('user/apply_new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

     /**
     * 授权申请编辑
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_LOADOFFICER_MANAGER') or has_role('ROLE_ADMIN_HPL')")
     * @Route("/apply_edit/{id}", name="user_apply_edit")
     * @Method({"GET", "POST"})
     */
    public function applyEditAction(Request $request, Apply $apply)
    {
        $editForm = $this->createForm('AppBundle\Form\ApplyType', $apply);
        $user = $this->getUser();
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $grade = $apply->getGrade();
            $mobile = $apply->getMobile();
            $agencyRelUser = $em->getRepository("AppBundle:User")->findOneBy(array('mobile'=>$mobile, 'enabled'=>1));
            $agencyRel = null;
            if(!empty($agencyRelUser)) {
                $agencyRel = $em->getRepository("AppBundle:AgencyRel")->findBy(array('user'=>$agencyRelUser->getId()));
            }
            //授权关系逻辑判断部分
            if(!empty($agencyRel)) {
                if($grade == 1) {
                        $error = new FormError('该手机号码已绑定授权关系,请勿重复创建！');
                        $form->get('mobile')->addError($error);
                        return $this->render('user/apply_new.html.twig', array(
                            'user' => $user,
                            'form' => $form->createView(),
                        ));
                } elseif($grade == 2) {
                    $company = $apply->getCompany()->getId();
                    foreach($agencyRel as $value) {
                        $hasCompany = $value->getCompany()->getId();
                        if($company == $hasCompany) {
                            $error = new FormError('该手机号已绑定授权关系,请勿重复创建！');
                            $form->get('mobile')->addError($error);
                            return $this->render('user/apply_new.html.twig', array(
                                            'user' => $user,
                                            'form' => $form->createView(),
                                        ));
                        }
                        if($value->getGrade() == 1 && $value->getGrade() == 0) {
                            $error = new FormError('该手机号已绑定授权关系,请勿重复创建！');
                            $form->get('mobile')->addError($error);
                            return $this->render('user/apply_new.html.twig', array(
                                            'user' => $user,
                                            'form' => $form->createView(),
                                        ));
                        }
                    }
                } elseif($grade == 3) {
                    $company = $apply->getCompany()->getId();
                    foreach($agencyRel as $value) {
                        $hasCompany = $value->getCompany()->getId();
                        if($company == $hasCompany) {
                            $error = new FormError('该手机号已绑定授权关系,请勿重复创建！');
                            $form->get('mobile')->addError($error);
                            return $this->render('user/apply_new.html.twig', array(
                                            'user' => $user,
                                            'form' => $form->createView(),
                                        ));
                        }
                        if($value->getGrade() == 1 && $value->getGrade() == 0) {
                            $error = new FormError('该手机号已绑定授权关系,请勿重复创建！');
                            $form->get('mobile')->addError($error);
                            return $this->render('user/apply_new.html.twig', array(
                                            'user' => $user,
                                            'form' => $form->createView(),
                                        ));
                        }
                    }
                }
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash(
                'notice',
                '编辑成功'
            );

            return $this->redirectToRoute('user_apply');
        }

        return $this->render('user/apply_edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * 授权申请失效
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_LOADOFFICER_MANAGER') or has_role('ROLE_ADMIN_HPL')")
     * @Route("/apply_invalid/{id}", name="user_apply_invalid")
     */
    public function applyInvalidAction(Request $request, Apply $apply)
    {

        $em = $this->getDoctrine()->getManager();
        $apply->setStatus('2');
        $em->flush();

        $this->addFlash(
            'notice',
            '操作成功'
        );

        return $this->redirectToRoute('user_apply');
    }  

    /**
     * h5授权申请
     * @Route("/apply_web/{rand}", name="user_apply_web")
     */
    public function applyWebAction(Request $request, Apply $apply)
    {
        $status = $apply->getStatus();
        $mobile = $apply->getMobile();
        $rand = $apply->getRand();
        $userName = 'YunJC_'.date('Ymd').$rand;
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->findOneBy(array('mobile'=>$mobile, 'enabled'=>1));
        $grade = $apply->getGrade();
        $agency = $apply->getAgency();
        $companyName = $agency->getCompany()->getCompany();
        $agencyName = $agency->getName();
        $data = [];
        $data['showordernum'] = $rand;
        $data['company'] = $apply->getCompany()->getId();
        $data['agency'] = $apply->getAgency()->getId();
        $data['message_extra'] = null;

        if($status == 2){
            $data['status'] = 2;
            $data['message'] = '此次授权失败，请联系管理员重新授权.';
        } elseif ($status == 1) {
            $data['status'] = 1;
            if($grade == 1) {
                $data['message'] = '您已授权成为'.$companyName.'管理员。';
            } elseif ($grade == 2) {
                $data['message'] = '您已授权成为'.$companyName.'-'.$agencyName.'(经销商)管理员。';
            } else {
                $data['message'] = '您已授权成为'.$companyName.'-'.$agencyName.'(经销商)采集员。';
            }
        } elseif ($status == 0) {
            $data['status'] = 0;
            $data['mobile'] = $mobile;
            $data['province'] = $apply->getProvince()->getName();
            $data['provinceId'] = $apply->getProvince()->getId();
            $data['city'] = $apply->getCity()->getName();
            $data['cityId'] = $apply->getCity()->getId();
            $data['name'] = $userName;
            $data['grade'] = $grade;
            if($grade == 1) {
                $data['message'] = '您已授权成为'.$companyName.'管理员。';
                $data['role'] = 'ROLE_ADMIN_HPL';
            } elseif ($grade == 2) {
                $data['message'] = '您已授权成为'.$companyName.'-'.$agencyName.'管理员。';
                $data['role'] = 'ROLE_LOADOFFICER_MANAGER';
            } else {
                $data['message'] = '您已授权成为'.$companyName.'-'.$agencyName.'采集员。';
                $data['role'] = 'ROLE_LOADOFFICER';
            }
            if(empty($user)) {
                $data['isRegister'] = 0;
                $data['message_extra'] = '授权成功后，您可以用手机号码'.$mobile.'登录系统，请在下边创建您的登录密码';
            } else {
                $data['isRegister'] = 1;
                $userNameOld = $user->getUserName();
                $data['message_extra'] = '授权成功后，您可以用手机号码'.$mobile.'登录系统(也可以用原登录账号'.$userNameOld.')，原来的密码不变';
            }
        }

        return $this->render('user/apply_web.html.twig', $data);
    }

    /**
     * 授权申请短信验证
     * @Route("/apply_verify/", name="user_apply_verify")
     */
    public function applyVerifyAction(Request $request)
    {
        $mobile = $request->request->get('mobile');

        if (!$mobile) {
            return new JsonResponse(array('success' => false, 'message' => 'error'));
        }   

        $sessionKey = $mobile;

        $utilMsg = $this->get("util.smverifycode");

        $result = $utilMsg->send($sessionKey, $mobile);

        if ($result) {
            return new JsonResponse(array('success' => true, 'message' => '短信发送成功'));
        } else {
            return new JsonResponse(array('success' => false, 'message' => '短信发送失败,请重新获取'));
        }
    }

    /**
     * 授权申请激活
     * @Route("/apply_save/", name="user_apply_save")
     */
    public function applySaveAction(Request $request)
    {
        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();

        if(empty($data['mobile'])) {
            return new JsonResponse(array('success' => false, 'message' => '手机号码不能为空,请重新申请'));
        }
        
        $session = $this->get('session');
        $msCode = $session ->get($data['mobile'].'_verifycode');
        if($msCode != $data['msCode']) {
            return new JsonResponse(array('success' => false, 'message' => '验证码错误,请重新填写'));
        }

        if($data['isRegister'] == 0) {
            $isUser = $em->getRepository("AppBundle:User")->findOneBy(array('username'=>$data['name']));
            if(!empty($isUser)) {
                return new JsonResponse(array('success' => false, 'message' => '用户名已被占用,请联系管理员修改'));
            }
            if(empty($data['password'])) {
                return new JsonResponse(array('success' => false, 'message' => '密码不能为空,请重新填写'));
            } else {
                $user = new User();
                $agencyRel = new AgencyRel(); 
                $apply = $em->getRepository("AppBundle:Apply")->findOneBy(array('mobile'=>$data['mobile'], 'rand'=>$data['rand']));
                $company = $em->getRepository("AppBundle:Config")->find($data['company']);
                $agency = $em->getRepository("AppBundle:Agency")->find($data['agency']);
                $province = $em->getRepository("AppBundle:Province")->find($data['provinceId']);
                $city = $em->getRepository("AppBundle:City")->find($data['cityId']);
                $creater = $em->getRepository("AppBundle:User")->findOneBy(array('name'=>$apply->getCreater()));

                $user->setUsername($data['name']);
                $user->setMobile($data['mobile']);
                $user->setProvince($province);
                $user->setCity($city);
                $user->setRoles(array('roles'=>$data['role']));
                $user->setPlainPassword($data['password']);
                $user->setCreater($creater);
                $user->setName($data['trueName']);

                $em->persist($user);
                $em->flush();

                $agencyRel->setCreater($creater);
                $agencyRel->setCreatedAt(new \DateTime());
                $agencyRel->setGrade($data['grade']);
                $agencyRel->setUser($user);
                $agencyRel->setCompany($company);
                $agencyRel->setAgency($agency);

                $em->persist($agencyRel);
                $apply->setStatus(1);
                $em->flush();

                return new JsonResponse(array('success' => true, 'message' => '授权成功'));
            }
        } elseif($data['isRegister'] == 1) {
            //已注册用户创建agencyRel
            $agencyRel = new AgencyRel(); 
            $apply = $em->getRepository("AppBundle:Apply")->findOneBy(array('mobile'=>$data['mobile'], 'rand'=>$data['rand']));
            $user = $em->getRepository("AppBundle:User")->findOneBy(array('mobile'=>$data['mobile']));
            $company = $em->getRepository("AppBundle:Config")->find($data['company']);
            $agency = $em->getRepository("AppBundle:Agency")->find($data['agency']);
            $creater = $em->getRepository("AppBundle:User")->findOneBy(array('name'=>$apply->getCreater()));
            $grade = $data['grade'];
            if($grade != 1) {
            $gradeArr = $em->getRepository('AppBundle:agencyRel')->findAgencyRelsGrade($user);
            foreach ($gradeArr as $value) {
                $arr[] = $value['grade'];
            }
            array_push($arr,$grade);
            $grade = min($arr);
            switch ($grade) {
                case 2:
                    $data['role'] = 'ROLE_LOADOFFICER_MANAGER';
                    break;
                case 2:
                    $data['role'] = 'ROLE_LOADOFFICER';
                    break;
                default:
                    break;
                }
            }
            $user->setRoles(array('roles'=>$data['role']));
            $em->flush();

            $agencyRel->setCreater($creater);
            $agencyRel->setCreatedAt(new \DateTime());
            $agencyRel->setGrade($data['grade']);
            $agencyRel->setUser($user);
            $agencyRel->setCompany($company);
            $agencyRel->setAgency($agency);
            $em->persist($agencyRel);
            $apply->setStatus(1);
            $em->flush();

            return new JsonResponse(array('success' => true, 'message' => '授权成功'));
        }
    }

    /**
     * 授权申请查询经销商关联省份
     * @Route("/apply_agency/", name="user_apply_agency")
     */
    public function applyAgencyAction(Request $request)
    {
        $agencyId = $request->request->get('agency');
        $em = $this->getDoctrine()->getManager();

        $agency = $em->getRepository("AppBundle:Agency")->find($agencyId);
        $provinceId = $agency->getProvince()->getId();
        $cityId = $agency->getCity()->getId(); 

        return new JsonResponse(array('success' => true, 'message' => '查询成功', 'province' => $provinceId, 'city' => $cityId));
    }
}
