<?php

namespace AppBundle\Third;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Liyang
{
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    // 这是从youyiche项目copy过来的
    public function matchVin($vin)
    {
        $c = new \soapclient('http://115.159.52.248:8088/webService/NIDCXService.asmx?wsdl', ['trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE]);
        $response = $c->GetCXInfoByVIN(['vin' => $vin]);
        $xml = simplexml_load_string('<xml>'.$response->GetCXInfoByVINResult.'</xml>');
        $error_message = [
            'E1' => 'VIN码不是17位',
            'E2' => 'VIN码包含了错误字符‘O’，‘I’，‘Q’！',
            'E3' => '此VIN码是奔驰的底盘号非标准VIN码',
            'E4' => '此VIN码是非国标码',
            'E5' => '此VIN码不符合校验规则或为非国标码',
            'E6' => '出现异常，请联系又一车IT部！',
            'E7' => 'IP验证不通过'
        ];
        $check = $xml->Check;
        if ($check != 'E0') {
            throw new \Exception($error_message["$check"], 1);
        }
        $ret = $xml->Model_ID;
        if (!is_object($ret)) {
            $ret = [$ret];
        }
        return (array)$ret;
    }
}