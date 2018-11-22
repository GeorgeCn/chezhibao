<?php

namespace AppBundle\util;

class AppToken{

    const SEPARATOR = '^';

    protected $signature;
    protected $begintime;
    protected $endtime;
    protected $data;

    protected $errcode = 0;
    protected $errmsg = '';


    public static function createToken($userId) {
        $o = new static;
        $now = time();
        $o->begintime = $now;
        $o->endtime = $now + 7200;

        $o->data = $userId;
        if (!$o->data) {
            $o->errcode = 30;
            $o->errmsg = '用户名或密码错误';
        }

        return $o;
    }

    /**
     * @param string $token
     * @return static
     */
    public static function getToken($token) {
        $o = new static;

        $o->signature = substr($token, 0, 32);
        $o->begintime = (int)substr($token, 32, 10);
        $o->endtime = (int)substr($token, 42, 10);
        $o->data = substr($token, 52);

        return $o;
    }

    /**
     * @return string
     */
    public function getUid() {
        return $this->getData();
    }

    public function verify() {
        foreach(['signature', 'begintime', 'endtime', 'data'] as $v) {
            if (null === $this->$v) {
                throw new Exception(get_called_class().'::'.$v.' must be assigned before');
            }
        }
        $signature = static::getSign($this->data, $this->begintime, $this->endtime, static::salt());
        if ($signature != $this->signature) {
            return false;
        }
        $now = time();
        if ($this->begintime == $this->endtime || ($this->begintime <= $now && $now <= $this->endtime)) {
            return true;
        } else {
            return false;
        }
    }

    public function getString() {
        foreach(['begintime', 'endtime', 'data'] as $v) {
            if (null === $this->$v) {
                throw new Exception(get_called_class().'::'.$v.' must be assigned before');
            }
        }
        if ($this->signature === null) {
            $this->signature = static::getSign($this->data, $this->begintime, $this->endtime, static::salt());
        }
        return $this->signature.$this->begintime.$this->endtime.$this->data;
    }

    /**
     * @param string $data
     * @param int $begin time begin
     * @param int $end time end
     * @param string $salt
     * @return string
     */
    protected static function getSign($data, $begin, $end, $salt) {
        return md5($data . self::SEPARATOR . $begin . self::SEPARATOR . $end . self::SEPARATOR . $salt);
    }

    public function __get($name) {
        $m = 'get'.$name;
        if (!method_exists($this, $m)) {
            throw new Exception(get_called_class().'::'.$name.'access deny');
        } else {
            return $this->$m();
        }
    }

    public function __set($name, $value) {
        $m = 'set'.$name;
        if (!method_exists($this, $m)) {
            throw new Exception(get_called_class().'::'.$name.'access deny');
        } else {
            $this->$m($value);
        }
    }

    /**
     * @return int
     */
    public function getBegintime() {
        return $this->begintime;
    }

    /**
     * @param int $begintime
     */
    public function setBegintime($begintime) {
        $this->begintime = (int)$begintime;
    }

    /**
     * @return int
     */
    public function getEndtime() {
        return $this->endtime;
    }

    /**
     * @param int $endtime
     */
    public function setEndtime($endtime) {
        $this->endtime = (int)$endtime;
    }

    /**
     * @return string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getErrcode() {
        return $this->errcode;
    }

    /**
     * @return string
     */
    public function getErrmsg() {
        return $this->errmsg;
    }

    public static function salt() {
        return '&@cEOs@%^7735435';
    }
}