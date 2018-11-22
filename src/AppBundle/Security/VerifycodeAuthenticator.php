<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class VerifycodeAuthenticator implements SimpleFormAuthenticatorInterface
{
    private $encoderFactory;
    private $userChecker;
    private $container;

    public function __construct(EncoderFactoryInterface $encoderFactory, UserCheckerInterface $userChecker) {
        $this->encoderFactory = $encoderFactory;
        $this->userChecker = $userChecker;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey) {
        $verifycode_login_swtich = $this->container->getParameter("verifycode_login_switch");
        if ($verifycode_login_swtich === true && !$token->validate()) {
            throw new AuthenticationException('验证码错误！');
        }
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new AuthenticationException('用户名或者密码错误！');
        }
        $this->userChecker->checkPreAuth($user);
        $encoder = $this->encoderFactory->getEncoder($user);
        $passwordValid = $encoder->isPasswordValid( $user->getPassword(), $token->getCredentials(), $user->getSalt() );
        
        if ($passwordValid) {
            $this->userChecker->checkPostAuth($user);
            return new UsernamePasswordVerifyCodeToken( $token->getVerifycode(), $token->getverifycodeSession(), $user, $user->getPassword(), $providerKey, $user->getRoles() );
        }
        throw new AuthenticationException('用户名或者密码错误！');
    }

    public function supportsToken(TokenInterface $token, $providerKey) {
        return $token instanceof UsernamePasswordVerifyCodeToken && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        $verifycode_seesion_key = $this->container->getParameter("verifycode_login_key");
        $verifycode = $request->get("_verifycode");
        $verifycodeSession = $request->getSession()->get($verifycode_seesion_key)["phrase"];
        return new UsernamePasswordVerifyCodeToken($verifycode, $verifycodeSession, $username, $password, $providerKey); 
    }
}
