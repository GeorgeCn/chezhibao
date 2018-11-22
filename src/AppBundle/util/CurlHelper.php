<?php

namespace AppBundle\util;

class CurlHelper
{
    public function get($path, $params = [], $headers = [])
    {
        $url = join("?", [$path, http_build_query($params)]);
        $c = curl_init();
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        $ret = curl_exec($c);
        curl_close($c);
        if ($ret === false) {
            return false;
        }
        $ret = json_decode($ret, true);

        return $ret;
    }

    public function post($url, $params, $headers = [], $port = 80)
    {
        $tmp = parse_url($url);
        if (isset($tmp["port"])) {
            $port = $tmp["port"];
        }

        $msg = is_array($params) ? http_build_query($params) : $params;
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_PORT, $port);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, $msg);
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        $ret = curl_exec($c);
        curl_close($c);

        return $ret;
    }
}