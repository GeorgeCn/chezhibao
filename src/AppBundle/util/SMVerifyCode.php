<?php

namespace AppBundle\util;
use AppBundle\Traits\ContainerAwareTrait;

class SMVerifyCode
{
    use ContainerAwareTrait;

    public function send($sessionKey, $to, $ttl=60){
        $session = $this->get("session");
        $code = substr(str_shuffle("01234567890123456789"), 0, 4);
        $timestamp = time();
        if ($session) {
            $origin_ttl = $session->get("{$sessionKey}_verifycode_ttl", "");
            if ($timestamp <= $origin_ttl) {
                return false;
            }
            $session->set("{$sessionKey}_verifycode", $code);
            $session->set("{$sessionKey}_verifycode_ttl", $timestamp + $ttl);
        }

        $this->get('app.third.sms')->send('yjc_sms_code', $to, [$code]);

        return $code;
    }

    public function validate($sessionKey, $code){
        if (!$code) {
            return false;
        }
        $timestamp = time();
        $session = $this->get("session");
        $origin_ttl = $session->get("{$sessionKey}_verifycode_ttl");
        if ($timestamp > $origin_ttl) {
            return false;
        }
        $value = $session->get("{$sessionKey}_verifycode");
        if ($code === $value) {
            $session->remove("{$sessionKey}_verifycode");
            $session->remove("{$sessionKey}_verifycode_ttl");
            return true;
        }
        return false;
    }
}
