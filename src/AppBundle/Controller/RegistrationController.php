<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\User;

/**
 * Controller managing the registration
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class RegistrationController extends BaseController
{
    public function registerAction(Request $request)
    {
        return $this->redirectToRoute('dashboard');

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            // 注册的用户账号类型是试用账号，分配的信贷员角色，记录ip地址，设置过期时间31天
            $user->setType(User::TYPE_TRIAL);
            $user->addRole('ROLE_LOADOFFICER');
            $user->setSource($request->getClientIp());
            
            $dateTime = new \DateTime();
            $dateTime->modify('+31 day');
            $user->setCredentialsExpireAt($dateTime);

            $userManager->updateUser($user);

            // if (null === $response = $event->getResponse()) {
            //     $url = $this->generateUrl('fos_user_registration_confirmed');
            //     $response = new RedirectResponse($url);
            // }

            // $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            // return $response;

            return new JsonResponse(array('success' => true, 'message' => 'ok'));
        }

        // 如果通过ajax提交的有错误信息，只返回第一个错误信息给前端
        if ($request->request->get('submitStep2')) {
            $errors = $form->getErrors(true);

            if (count($errors) > 0) {
                $firstErrorMsg = $errors[0]->getMessage();

                return new JsonResponse(array('success' => false, 'message' => $firstErrorMsg));
            }
        }

        return $this->render('FOSUserBundle:Registration:register.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * 发短信给用户
     */
    public function sendMsgAction(Request $request)
    {
        $mobile = $request->request->get('mobile');

        if (!$mobile) {
            return new JsonResponse(array('success' => false, 'message' => 'error'));
        }

        $users = $this->getDoctrine()->getRepository('AppBundle:User')->findUserByMobile($mobile);

        if (count($users) > 0) {
            return new JsonResponse(array('success' => false, 'message' => '手机号码在系统中已存在，不能重复'));
        }

        $sessionKey = $mobile;

        $utilMsg = $this->get("util.smverifycode");

        $result = $utilMsg->send($sessionKey, $mobile);

        if ($result) {
            return new JsonResponse(array('success' => true, 'message' => '短信已发送成功'));
        } else {
            return new JsonResponse(array('success' => false, 'message' => '失败'));
        }
    }

    /**
     * 验证用户提交的短信是否正确
     */
    public function validateMsgAction(Request $request)
    {
        $mobile = $request->request->get('mobile');
        $code = $request->request->get('code');

        $utilMsg = $this->get("util.smverifycode");

        $result = $utilMsg->validate($mobile, $code);

        if (true === $result) {
            return new JsonResponse(array('success' => true, 'message' => '短信验证成功'));
        } else {
            return new JsonResponse(array('success' => false, 'message' => '短信验证失败'));
        }
    }
}
