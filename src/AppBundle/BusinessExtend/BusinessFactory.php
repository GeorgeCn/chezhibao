<?php

namespace AppBundle\BusinessExtend;
use AppBundle\BusinessExtend\Hpl\HplMetadataManager;
use AppBundle\BusinessExtend\Hthy\MetadataManager AS HthyMetadataManager;
use AppBundle\BusinessExtend\Hthy\SyncData AS HthySyscData;
use AppBundle\BusinessExtend\Mljr\MljrMetadataManager;
use AppBundle\BusinessExtend\Pingan\PinganMetadataManager;
use AppBundle\BusinessExtend\Kfcj\KfcjMetadataManager;
use AppBundle\BusinessExtend\Jgqc\JgqcMetadataManager;
use AppBundle\BusinessExtend\Ztr\ZtrMetadataManager;
use AppBundle\BusinessExtend\General\GeneralMetadataManager;
use AppBundle\BusinessExtend\General\GeneralSyncInfo;

use AppBundle\Model\MetadataManager;
use AppBundle\Entity\Config;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * 业务工厂
 */
class BusinessFactory
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 获取各公司metadataManager
     */
    public function getMetadataManager($company = null)
    {
        $video = $this->getFieldPolicy($company);
        if (Config::COMPANY_MLJR === $company) {
            return new MljrMetadataManager($video['video']);
        } elseif (Config::COMPANY_KFCJ === $company) {
            return new KfcjMetadataManager($video['video']);
        } elseif (Config::COMPANY_JGQC === $company) {
            return new JgqcMetadataManager($video['video']);
        } elseif (Config::COMPANY_HTHY === $company) {
            return new HthyMetadataManager($video['video']);
        } elseif (Config::COMPANY_PINGAN === $company) {
            return new PinganMetadataManager($video['video']);
        } elseif (Config::COMPANY_ZTR === $company) {
            return new ZtrMetadataManager($video['video']);
        } elseif (Config::COMPANY_HPL === $company || Config::COMPANY_HPL_CBT === $company) {
            return new HplMetadataManager($video['video']);
        }

        return new GeneralMetadataManager($video['video']);
    }

    /**
     * 获取各公司显某些示字段的策略
     */
    public function getFieldPolicy($company = null, $agencyName = '')
    {
        $fields = [
            'valuation' => false, // 预售价格
            'businessNumber' => false, // 业务流水号
            'fsMenu' => false, // 复审菜单
            'ywglMenu' => true, // 业务管理菜单
            'showExamer' => false, // 审核师
            'useraskQuertion' => false, // 车主自述
            'remark' => false, //app 端
            'video' => true, //app 端 20171107新增需求
            'report' => false, // 检测报告
            'reportPrice' => false, //评估报告中车辆价格影响因素
            'reportPriceTrend' => false, //评估报告中车辆未来的价格走势
            'recheckButton' => true, //复审菜单订单列表中的复审按钮是否可以点击

            'purchasePricePc' => false, //收购价（pc端）
            'sellPricePc' => false, //销售价（pc端）
            'futurePricePc' => false, //未来价格（pc端）
            'componentRefuse' => false, //线上标准是否拒绝

            'purchasePriceApp' => false, //收购价（app端）
            'sellPriceApp' => false, //销售价（app端）
            'futurePriceApp' => false, //未来价格（app端）
            'importable' => true, //是否可从相册导入
        ];

        $ac = $this->container->get('security.authorization_checker');

        try {
            $result = $ac->isGranted(new Expression('has_role("ROLE_LOADOFFICER") or has_role("ROLE_LOADOFFICER_MANAGER") or has_role("ROLE_EXAMER_HPL")'));
        } catch (\Exception $e) {
            $result = false;
        }

        // 评估报告，维修记录
        $fields['report'] = !$result;
        $fields['maintain'] = !$result;

        if ($company) {
            $config = $this->container->get('doctrine')->getRepository('AppBundle:Config')->findOneByCompany($company);
            if ($config) {
                $policy = $config->getPolicy();
                if ($policy) {
                    foreach ($policy['show'] as $v) {
                        if (isset($fields[$v])) {
                            $fields[$v] = true;
                        } elseif (false !== strpos($v, 'Loadofficer') && $ac->isGranted('ROLE_LOADOFFICER')) {
                            $start = strpos($v, 'Loadofficer');
                            $newV = substr($v, 0, $start);
                            $fields[$newV] = true;
                        } elseif (false !== strpos($v, 'Lmanager') && $ac->isGranted('ROLE_LOADOFFICER_MANAGER')) {
                            $start = strpos($v, 'Lmanager');
                            $newV = substr($v, 0, $start);
                            $fields[$newV] = true;
                        } elseif (false !== strpos($v, 'Ehpl') && $ac->isGranted('ROLE_EXAMER_HPL')) {
                            $start = strpos($v, 'Ehpl');
                            $newV = substr($v, 0, $start);
                            $fields[$newV] = true;
                        }
                    }
                }
                $needVideo = $config->getNeedVideo();
                $fields['video'] = $needVideo;
            }
        }

        try {
            if ($ac->isGranted(new Expression('has_role("ROLE_ADMIN")'))) {
                $fields['purchasePrice'] = true;
                $fields['sellPrice'] = true;
                $fields['report'] = true;
                $fields['maintain'] = true;
            }
        } catch (\Exception $e) {
            
        }


        //业务管理按钮和是否显示审核师字段，由于不需经常变动，暂没在数据库中动态配置，保留之前的逻辑 
        switch ($company) {
            case Config::COMPANY_JGQC:
                $fields['ywglMenu'] = false;
                $fields['useraskQuertion'] = true;
                break;

            case Config::COMPANY_KFCJ:
                $fields['useraskQuertion'] = true;
                break;

            case Config::COMPANY_PINGAN:
                $fields['recheckButton'] = false;
                $fields['remark'] = true;
                $fields['componentRefuse'] = true;
                $fields['importable'] = false;
                break;

            case Config::COMPANY_HPL:
                if ($agencyName === '江苏车置宝信息科技股份有限公司') {
                    $fields['futurePricePc'] = true;
                    $fields['futurePriceApp'] = true;
                }
                break;

            default:
                if (false !== strpos($company, '又一车') or false !== strpos($company, '麦拉') ) {
                    $fields['showExamer'] = true;
                }
                $fields['remark'] = true;
                break;
        }

        return $fields;
    }

    /**
     * 各公司获取数据入口
     */
    public function getSystemSyncObject($company = null)
    {
        if (Config::COMPANY_HTHY === $company) {
            return new HthySyscData();
            // 等海通新系统上线，就用下面通用的对象
            // return new GeneralSyncInfo();
        }

        return new GeneralSyncInfo();
    }
}