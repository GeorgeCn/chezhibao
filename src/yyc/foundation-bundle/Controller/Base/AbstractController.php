<?php
namespace YYC\FoundationBundle\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

/**
 * 第三方验证
 *
 * 暂时
 */
class AbstractController extends Controller
{

    protected $_users = null;

    /**
     * 验证是否登录
     */
    protected function isLogin()
    {
        return $this->_users = $this->getUser();
    }

}