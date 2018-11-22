<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /**
     * @Route("/verify_code/{key}", name="get_verify_code")
     */
    public function getVerifyCodeAction(Request $request, $key)
    {
        $options = $this->container->getParameter('gregwar_captcha.config');
        $session = $request->getSession();

        /* @var \Gregwar\CaptchaBundle\Generator\CaptchaGenerator $generator */
        $generator = $this->container->get('gregwar_captcha.generator');

        $persistedOptions = $session->get($key, array());
        $options = array_merge($options, $persistedOptions);

        $phrase = $generator->getPhrase($options);
        $generator->setPhrase($phrase);
        $persistedOptions['phrase'] = $phrase;
        $session->set($key, $persistedOptions);

        $response = new Response($generator->generate($options));
        $response->headers->set('Content-type', 'image/jpeg');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }
}
