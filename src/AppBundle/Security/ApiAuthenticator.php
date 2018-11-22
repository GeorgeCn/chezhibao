<?php
/**
 * HPL 第三方业务接口防火墙
 * 远程监测车辆数据 需 第三方提供指定ip 获取接口数据
 *
 * 等待接口服务与第三方对接的策略
 * 1. Headers
 * 2. Databases config tables
 * 3. 路由匹配 Config 常量
 * 4. Params
 */
namespace AppBundle\Security;

use AppBundle\Entity\Config;
use AppBundle\Traits\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    use ContainerAwareTrait;

    private $_req;
    private $_status;
    private $_company;

    /**
     * create token
     * Get Request info and keys
     *
     * @param Request $request
     * @param $providerKey
     * @return PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey)
    {
        //检测url
        $url = explode('/', $request->server->get('REQUEST_URI'));
        if(is_array($url)){
            for ($i=0;$i<=count($url);$i++){
                if($url[$i] != null && $url[$i] != 'app_dev.php'){
                    $this->_company = $url[$i];
                    break;
                }
            }
        }
        //测试获取来源内容  策略确认后更改验证规则
        if (empty($this->_company)) {
            $this->_status = 404;
            throw new CustomUserMessageAuthenticationException(
                sprintf("Where are you from")
            );
        }
        //获取来源ip
        if (!$request->server->get('REMOTE_ADDR')) {
            throw new BadCredentialsException();
        }
        $this->_req = $request;
        return new PreAuthenticatedToken(
            'api.',
            $request->server->get('REMOTE_ADDR'),
            $providerKey
        );
    }

    /**
     * start authenticate
     *
     * @param TokenInterface $token
     * @param UserProviderInterface $userProvider
     * @param $providerKey
     * @return PreAuthenticatedToken
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $model = $this->getRepo('AppBundle:Config')->findOneByCompany($this->getCompanyName());
        if (!$model) {
            $this->_status = 404;
            throw new CustomUserMessageAuthenticationException(
                sprintf("I don't know you !")
            );
        }
        $ipArr = explode(',', $model->getInfo()['ip']);
        $apiKey = $token->getCredentials();
        if (array_search($apiKey, $ipArr) === false) {
            //$this->_status = 403;
            //throw new CustomUserMessageAuthenticationException(
            //    sprintf('The only token is not valid credentials ')
            //);
        }
        return new PreAuthenticatedToken(
            $providerKey,
            $apiKey,
            $providerKey,
            ['ROLE_BUSINESS_API']
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * response json
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(
            [
                'success' => false,
                'error_msg' => $exception->getMessage(),
            ]
        );
    }

    /**
     * 说明： 这个函数是因为我不知道平安就上线。。。。。。
     * 根据url查找公司名称
     */
    public function getCompanyName()
    {
        $companyArr = array(
            'pingan' => Config::COMPANY_PINGAN,
            'hpl' => Config::COMPANY_HPL,
            'mljr' => Config::COMPANY_MLJR,
            'hthy' => Config::COMPANY_HTHY,
            'kfcj' => Config::COMPANY_KFCJ,
            'jgqc' => Config::COMPANY_JGQC,
            'ztr' => Config::COMPANY_ZTR
        );
        if (isset($companyArr[$this->_company])) {
            return $companyArr[$this->_company];
        } else {
            $this->_status = 404;
            throw new CustomUserMessageAuthenticationException(
                sprintf("I don't know you .")
            );
        }
    }
}