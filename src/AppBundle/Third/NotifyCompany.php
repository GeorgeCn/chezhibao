<?php

namespace AppBundle\Third;

use AppBundle\Traits\ContainerAwareTrait;
use AppBundle\Entity\Config;

/**
 * 审核成功后根据公司表的配置来通知对应的公司
 */
class NotifyCompany
{
    use ContainerAwareTrait;

    /**
     * 通知公司
     */
    public function noticeCompany($orderNo)
    {
        //根据评估单号找到该订单的公司名字，查询公司配置表看是否有对应的通知url
        $order = $this->getRepo('AppBundle:Order')->findOneByOrderNo($orderNo);
		if(empty($order)){
			return false;
		}
        $company = $order->getCompany()->getCompany();
        if(!in_array($company, [Config::COMPANY_HPL, Config::COMPANY_HPL_CBT, Config::COMPANY_PINGAN])) {
            $syncObject = $this->get('app.business_factory')->getSystemSyncObject($company);
            return $syncObject->systemSyncNotice($order, $company, $this->container);
        }
        $companyUrl = $this->checkNoticeByCompanyConfig($company);

        if (!$companyUrl) {
            return false;
        }

        $status = false;

        switch ($company) {
            case Config::COMPANY_HPL:
            case Config::COMPANY_HPL_CBT:
                $newUrl = $companyUrl.$orderNo;
                echo date('Y-m-d H:i:s').": $newUrl\n";
                $result = $this->httpGet($newUrl);

                if ($result){
                    // 如果返回的结果没有包含successfully字符串，说明通知失败,如果包含existed说明已通知过
                    if (strpos($result, 'successfully') || strpos($result, 'existed')) {
                        $status = true;
                    }

                    if (false === $status) {
                        $this->get('logger')->error('companyNotice:'.$result);

                        return false;
                    } else {
                        return true;
                    }
                } else {
                    $this->get('logger')->error('companyNotice:'.$result);

                    return false;
                }

            case Config::COMPANY_PINGAN:
                // 发送请求到平安的逻辑，并处理平安返回的结果
                $std3des = $this->get('util.std3des');
                $std3des->init($key = 'JMS030!QLF8D85-ADA@E52DP');
                $pgdh = $std3des->encrypt($orderNo);
                $params['parameters.common'] = array("mySite" => "site");
                $params['alert.yyc.external'] = array(
                    'header' => array(
                        "transNo" => $orderNo.'_'.rand(),
                        "orgCode" => "G001",
                        "signature" => '',
                        "transTime" => '',
                        "version" => "1.0"
                    ),
                    'data' => $pgdh,
                );

                $json = json_encode($params);
                $url = $companyUrl.$json;
                echo date('Y-m-d H:i:s').": $url\n";
                $jsonResult = $this->httpGet($url);

                if ($jsonResult){
                    $result = json_decode($jsonResult);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $data = $result->data;
                        if ($data) {
                            $temp = 'alert.yyc.external';
                            if ('E00000' === $data[0]->$temp->errCode) {
                                $status = true;
                            }
                        }
                    }

                    if (false === $status) {
                        $this->get('logger')->error('companyNotice:'.$jsonResult);

                        return false;
                    } else {
                        return true;
                    }
                } else {
                    $this->get('logger')->error('companyNotice:'.$jsonResult);

                    return false;
                }

            default:
                return true;
        }
    }

    /**
     * 当审核通过后根据公司名称来判断是否需要通知对应的公司
     * 
     */
    public function checkNoticeByCompanyConfig($company = null)
    {
        //首先判断parameter.yml里是否开启了通知的开关
        $switch = $this->getParameter('notice_company');

        if (!$switch) {
            return false;
        }

        $companyConfig = $this->getDoctrine()->getRepository('AppBundle:Config')->findOneByCompany($company);

        if ($companyConfig) {
            if (isset($companyConfig->getParameter()['enabled'])) {
                if (!$companyConfig->getParameter()['enabled']) {
                    return false;
                }

                $companyUrl = $companyConfig->getParameter()['k1'];

                return $companyUrl;
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    /**
     * curl模拟get请求
     */
    public function httpGet($url)
    {
        //初始化
        $ch = curl_init();

        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        // 最长5分钟
        curl_setopt($ch, CURLOPT_TIMEOUT,300); 
        //执行并获取请求内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);

        return $output;
    }
}
