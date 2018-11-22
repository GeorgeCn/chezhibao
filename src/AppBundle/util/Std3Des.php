<?php

namespace AppBundle\util;

use AppBundle\Traits\ContainerAwareTrait;

/**
 * 3des 对称加密解密，与 java c#兼容
 * $iv cbc 分组模式的偏移量，没有则使用 ecb 模式
 */
class Std3Des
{
    use ContainerAwareTrait;

    private $key  = "";
    private $iv   = "";
    private $mode = MCRYPT_MODE_CBC;

    /**
     * 传递二个已经进行 base64_encode 的 key 与 iv
     *
     * @param string $key
     * @param string $iv
     */
    public function init($key, $iv = null) 
    {
        if (empty($key)) {
            echo 'key is not valid';
            exit();
        }

        if (!$iv) {
            $this->mode = MCRYPT_MODE_ECB;
        }

        $this->key = $key;
        $this->iv  = $iv;
    }

    /**
     * 加密
     * @param <type> $value
     * @return <type>
     */
    public function encrypt($value)
    {
        $td    = mcrypt_module_open(MCRYPT_3DES, '', $this->mode, '');
        $iv    = $this->mode == MCRYPT_MODE_CBC ? base64_decode($this->iv) : mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $value = $this->PaddingPKCS7($value);
        $key = $this->key;

        mcrypt_generic_init($td, $key, $iv);
        $dec   = mcrypt_generic($td, $value);
        $ret   = base64_encode($dec);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $ret;
    }

    /**
     * 解密
     * @param <type> $value
     * @return <type>
     */
    public function decrypt($value)
    {
        $td  = mcrypt_module_open(MCRYPT_3DES, '', $this->mode, '');
        $iv  = $this->mode == MCRYPT_MODE_CBC ? base64_decode($this->iv) : mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $key = $this->key;

        mcrypt_generic_init($td, $key, $iv);
        $ret = trim(mdecrypt_generic($td, base64_decode($value)));
        $ret = $this->UnPaddingPKCS7($ret);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return $ret;
    }

    private function PaddingPKCS7($data)
    {
        $block_size   = mcrypt_get_block_size('tripledes', $this->mode);
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);

        return $data;
    }

    private function UnPaddingPKCS7($text)
    {
        $pad = ord($text{strlen($text) - 1});

        if ($pad > strlen($text)) {
            return false;
        }

        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }

        return substr($text, 0, -1 * $pad);
    }

    /**
     * $params待签名参数
     * 用的是RSA签名
     * 最后的签名，需要用base64编码
     * return Sign签名
     */
    public function getSign($params)
    {
        // $priKeyFile = 'yyc.cer';
        $priKeyFile = 'yyc2.pem';
        // $priKeyFile = 'rsa_private_key.pem';

        //读取私钥文件
        $priKey = file_get_contents($priKeyFile);
        // var_dump($priKey);exit;
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥,返回的是resource
        $res = openssl_get_privatekey($priKey);

        $data= $params;

        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res);
        //释放资源
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);

        return $sign;
    }
}