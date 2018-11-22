<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimpleFormAuthenticatorInterface;
use AppBundle\Traits\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;

class IpAuthenticator implements SimpleFormAuthenticatorInterface
{
    use ContainerAwareTrait;

    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            // CAUTION: this message will be returned to the client
            // (so don't put any un-trusted messages / error strings here)
            throw new CustomUserMessageAuthenticationException('系统不存在该账号！');
        }
        // locked，disabled，expired的账号不能登录系统
        $this->get('security.user_checker.rest_api')->checkPreAuth($user);
        if ($user->getAgencyRels()[0] === null) {
            throw new CustomUserMessageAuthenticationException('用户公司必须存在！');
        }

        $passwordValid = $this->get('security.password_encoder')->isPasswordValid($user, $token->getCredentials());

        if ($passwordValid) {
            if ($this->getParameter('ip_validate_switch') === true) {
                // 拥有'ROLE_EXAMER', 'ROLE_EXAMER_MANAGER角色的用户，白名单里有对应的ip才可以通过
                $roles = ['ROLE_EXAMER', 'ROLE_EXAMER_MANAGER'];
                $roleHierarchy = $this->getParameter('security.role_hierarchy.roles');
                $allRoles = array_keys($roleHierarchy);
                $exculdeRoles = array_diff($allRoles, $roles);

                $ret = array_intersect($roles, $user->getRoles());
                $exculdeRet = array_intersect($exculdeRoles, $user->getRoles());
                if ($ret && !$exculdeRet) {
                    $request = $this->requestStack->getCurrentRequest();
                    $clientIp = $request->getClientIp();
                    $whiteLists = $this->getRepo('AppBundle:WhiteList')->findAll();
                    foreach ($whiteLists as $whiteList) {
                        if ($this->get('util.str')->is($whiteList->getIp(), $clientIp) === true) {
                            return new UsernamePasswordToken(
                                $user,
                                $user->getPassword(),
                                $providerKey,
                                $user->getRoles()
                            );
                        }
                    }

                    throw new CustomUserMessageAuthenticationException(
                        '必须在公司内部才能登录系统！'
                    );
                }
            }

            return new UsernamePasswordToken(
                $user,
                $user->getPassword(),
                $providerKey,
                $user->getRoles()
            );
        }

        // CAUTION: this message will be returned to the client
        // (so don't put any un-trusted messages / error strings here)
        throw new CustomUserMessageAuthenticationException('用户名或密码错误！');
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken
            && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }
}