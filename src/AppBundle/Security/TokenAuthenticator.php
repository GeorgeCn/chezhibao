<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\util\AppToken;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /** * Called on every request. Return whatever credentials you want, * or null to stop authentication. */
    public function getCredentials(Request $request)
    {
        if (!$token = $request->headers->get('X-ACCESS-TOKEN')) {
            $token = "no token";
        }
        $userId = 0;
        $valid = false;

        $hplToken = AppToken::getToken($token);
        $valid = $hplToken->verify();
        if($valid){
            $userId = $hplToken->getUid();
            return ['token' => $token, 'valid' => $valid, 'user_id' => $userId];
        }

        // What you return here will be passed to getUser() as $credentials
        return ['token' => $token, 'valid' => $valid, 'user_id' => $userId];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if ($credentials["valid"] === false) {
            throw new BadCredentialsException('Bad credential.');
        }
        $userId = $credentials['user_id'];
        // if null, authentication will fail
        // if a User object, checkCredentials() is called
        return $this->em->getRepository('AppBundle:User')->find($userId);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($user->getAgencyRels()[0] === null) {
            return false;
        }

        if (!$user->isAccountNonLocked()) {
            return false;
        }

        if (!$user->isEnabled()) {
            return false;
        }

        if (!$user->isAccountNonExpired()) {
            return false;
        }
        return $credentials["valid"];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'code' => 1,
            'msg' => strtr($exception->getMessageKey(), $exception->getMessageData()),
            ];

        return new JsonResponse($data, 403);
    }

    /** * Called when authentication is needed, but it's not sent */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'code' => 1,
            'msg' => '需要认证',
        ];

        return new JsonResponse($data, 401);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
