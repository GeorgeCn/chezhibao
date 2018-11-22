<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UsernamePasswordVerifyCodeToken extends UsernamePasswordToken
{
    private $verifycode;
    private $verifycodeSession;

    public function __construct($verifycode, $verifycodeSession, $user, $credentials, $providerKey, array $roles = array())
    {
        if (empty($verifycode) || empty($verifycodeSession)) {
            //throw new \InvalidArgumentException('$verifycode must not be empty.');
        }

        $this->verifycode = $verifycode;
        $this->verifycodeSession = $verifycodeSession;

        parent::__construct($user, $credentials, $providerKey, $roles);
    }

    public function getVerifycode()
    {
        return $this->verifycode;
    }

    public function getVerifycodeSession()
    {
        return $this->verifycodeSession;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->verifycode, $this->verifycodeSession, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->verifycode, $this->verifycodeSession, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }

    // 验证“验证码”
    public function validate(){
        return strcasecmp($this->verifycode, $this->verifycodeSession) === 0;
    }
}