<?php

namespace AppBundle\Controller\Openapi;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController extends Controller
{
    /**
     * @Route("/token/gen", name="token_gen")
     */
    public function genAction(Request $request)
    {
        $errcode = 0;
        $errmsg = '';
        $tokenString = '';
        $username = $request->request->get('user');
        $password = $request->request->get('psw');

        if ($username === '' || $password === '') {
            $errcode = 2;
            $errmsg = '用户名或密码不能为空';
        } else {
            $user = $this->getDoctrine()->getRepository('AppBundle:User')->findUserByUsernameOrMobile($username);
            if ($user) {
                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                $valid = $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
                if ($valid === true) {
                    $userId = $user->getId();
                    $token = $this->get('util.token')->createToken($userId);
                    $errcode = $token->getErrcode();
                    $errmsg = $token->getErrmsg();
                    $tokenString = $token->getString();
                } else {
                    $errcode = 3;
                    $errmsg = '用户名或密码错误';
                }
            } else {
                $errcode = 4;
                $errmsg = '用户不存在';
            }
        }

        $ret = [
            'errcode'=>$errcode,
            'errmsg'=>$errmsg,
            'data'=>[
                'token'=>$tokenString
            ]
        ];

        return new JsonResponse($ret);
    }
}
