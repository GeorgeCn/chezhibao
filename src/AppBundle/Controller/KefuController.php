<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use AppBundle\Entity\User;

/**
 * 客服用来添加用户
 * 
 * @Security("has_role('ROLE_KEFU')")
 * @Route("/kefu")
 */
class KefuController extends Controller
{
    /**
     * Creates a new User entity.
     *
     * @Route("/new", name="kefu_user_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm('AppBundle\Form\KefuType', $user);

        // 初始化form这些字段的值
        $province = $this->getDoctrine()->getRepository('AppBundle:Province')->find(1);
        $city = $this->getDoctrine()->getRepository('AppBundle:City')->find(1);

        $form->get('province')->setData($province);
        $form->get('city')->setData($city);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // // 注册的用户账号类型是试用账号设置过期时间31天
            // $dateTime = new \DateTime();
            // $dateTime->modify('+31 day');
            // $user->setCredentialsExpireAt($dateTime);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash(
                'notice',
                '用户'.$user->getName().'新增成功!'
            );

            // return $this->redirectToRoute('user_show', array('id' => $user->getId()));
            return $this->redirectToRoute('kefu_user_new');
        }

        return $this->render('kefu/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }
}
