<?php

namespace AppBundle\util;

class SystemApiSign
{
    public function enSign($params)
    {
        ksort($params);
        $str = implode('', $params);
        return md5($str);
    }
}