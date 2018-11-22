<?php

namespace AppBundle\Model;

class MetadataManager
{
    protected $metadataMap = [];

    protected $video = false;

    const PLACEHOLDER = '如果没有以上选择项，请手动填写';

    public function getVersion()
    {
        return 8;
    }

    public function __construct($video)
    {
        $this->video = $video;
        $this->initMetadataMap([
            // picture
            new Metadata('k1', '登记证', 'imagelist', [], ['least' => 2, 'groups' => '证件照', 'subGroups' => ['accident_must']]),
            new Metadata('k2', '车辆左前45度', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k3', '前档玻璃(生产日期)', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must','accident5']]),
            new Metadata('k4', '左前门', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k5', '仪表', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k6', '左后门', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k7', '中控台', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k8', '内车顶', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k9', '中央扶手', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k10', '左后铰链', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident8', 'accident16']]),
            new Metadata('k11', '左后底板', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident8', 'accident9']]),
            new Metadata('k12', '右后底板', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident8', 'accident9']]),
            new Metadata('k13', '右后铰链', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident8', 'accident17']]),
            new Metadata('k131', '后围板', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图']),
            new Metadata('k14', '后档玻璃(生产日期)', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident7','accident_must']]),
            new Metadata('k15', '车辆右后45度', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_none']]),
            new Metadata('k16', '右后门', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_none']]),
            new Metadata('k17', '右前门', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k18', '右前水箱框架', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident1', 'accident3']]),
            new Metadata('k19', '右避震器座', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident4', 'accident11']]),
            new Metadata('k20', '左前水箱框架', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident1', 'accident2']]),
            new Metadata('k21', '左避震器座', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident4', 'accident10']]),
            new Metadata('k22', '铭牌', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k23', '附加', 'imagelist', [], ['groups' => '附加']),

            new Metadata('k30', '正前照', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k35', '左前门A柱铰链', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident12']]),
            new Metadata('k40', '方向盘左前侧位置', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k45', '座椅表面材质照', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_none']]),
            new Metadata('k50', '左前A柱内饰', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k55', '左后门B柱铰链', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident14']]),
            new Metadata('k60', '左侧B柱内饰', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k65', '后排中央扶手', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_none']]),
            new Metadata('k70', '侧面照', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k75', '后盖整体照', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_none']]),
            new Metadata('k80', '正后照', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k85', '油箱盖内侧', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_none']]),
            new Metadata('k90', '后排轮毂照', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident_must']]),
            new Metadata('k95', '右后门B柱铰链', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident15']]),
            new Metadata('k100', '右前门A柱铰链', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident13']]),
            new Metadata('k105', '右前纵梁', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident11']]),
            new Metadata('k110', '右前上纵梁', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident3', 'accident4', 'accident11']]),
            new Metadata('k115', '左前纵梁', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident10']]),
            new Metadata('k120', '左前上纵梁', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图', 'subGroups' => ['accident2', 'accident4', 'accident10']]),
            new Metadata('k125', '行驶证', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '行驶证']),

            new Metadata('append', '补充', 'imagelist', [], ['groups' => '补充']),

            new Metadata('k130', '后排座椅', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图']),
            new Metadata('k135', 'A/B/C柱内饰', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图']),
            new Metadata('k140', '水框全景照片', 'imagelist', [], ['least' => 1, 'most' => 1, 'groups' => '车型图']),

            // etra
            //"etra_"

            new Metadata('extra1', '车型', 'textArray2'),
            new Metadata('extra2', '公里数(km)', 'text2'),
            new Metadata('extra3', '驱动形式', 'text2'),
            new Metadata('extra4', '车辆情况', 'text2'),
            new Metadata('extra5', '机械情况', 'text2'),
            new Metadata('extra6', '事故点', 'textArray2', [], ['choices' => $this->getAccidents()]),
            new Metadata('extra7', '以下功能模块正常', 'textArray2'),
            new Metadata('extra8', '加装或改装以下装置', 'textArray2'),
            new Metadata('extra9', '火烧泡水情况', 'textArray2'),

            // backreason

            new Metadata('reason_1', '正前照', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/01正前照.png']),
            new Metadata('reason_60', '车辆左前45度', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/05左前45度.png']),
            new Metadata('reason_70', '前档玻璃(生产日期)', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/06前档玻璃(生产日期).png']),
            new Metadata('reason_150', '左前门', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/14左前门.png']),
            new Metadata('reason_160', '左前门A柱铰链', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/15左前门与A柱连接螺丝.png']),
            new Metadata('reason_6', '方向盘左前侧', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/06方向盘左前侧.png']),
            new Metadata('reason_7', '座椅表面材质照', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/07座椅表面材质照.png']),
            new Metadata('reason_8', '左前A柱内饰', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/08左前A柱内饰.png']),
            new Metadata('reason_170', '仪表', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/16仪表.png']),
            new Metadata('reason_180', '左后门', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/17左后门.png']),
            new Metadata('reason_12', '左侧B柱内饰', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/12左侧B柱内饰.png']),
            new Metadata('reason_190', '中控台', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/18中控台.png']),
            new Metadata('reason_14', '内车顶', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/14内车顶.png']),
            new Metadata('reason_320', '中央扶手', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B07后出风口.png']),
            new Metadata('reason_16', '后排中央扶手', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/16后排中央扶手.png']),
            new Metadata('reason_17', '侧面照', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/17侧面照.png']),
            new Metadata('reason_200', '左后铰链', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/19左后.png']),
            new Metadata('reason_210', '左后底板', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/20左后纵梁备胎框.png']),
            new Metadata('reason_220', '右后底板', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/21右后纵梁备胎框.png']),
            new Metadata('reason_230', '右后铰链', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/22右后.png']),
            new Metadata('reason_22', '后盖整体照', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/后盖整体照.png']),
            new Metadata('reason_90', '后挡玻璃(生产日期)', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/08后档玻璃(生产日期).png']),
            new Metadata('reason_24', '正后照', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/24正后照.png']),
            new Metadata('reason_80', '右后45度', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/07右后45度.png']),
            new Metadata('reason_26', '油箱盖内侧', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/26油箱盖内侧.png']),
            new Metadata('reason_27', '后排轮毂照', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/27后排轮毂照.png']),
            new Metadata('reason_240', '右后门', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/23右后门.png']),
            new Metadata('reason_250', '右前门', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/250右前门.png']),
            new Metadata('reason_260', '右前门A柱铰链', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/25右前门与A柱连接螺丝.png']),
            new Metadata('reason_120', '右前水箱框架', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/11右前水箱框架与上纵梁.png']),
            
            new Metadata('reason_110', '右避震器座', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/右前避震器座胶水及焊点.png']),
            new Metadata('reason_130', '左前水箱框架', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/12左前水箱框架与上纵梁.png']),
            
            new Metadata('reason_140', '左避震器座', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/左前避震器座胶水及焊点.png']),
            new Metadata('reason_40', '铭牌', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/04车辆铭牌.png']),
            new Metadata('reason_20', '登记证', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/02车辆产证.png']),
            new Metadata('reason_10', '行驶证', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/01行驶证正副本.png']),
            
            new Metadata('reason_11', '左后门B柱铰链', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/11左后门B柱铰链.png']),
            new Metadata('reason_29', '右后门B柱铰链', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/29右后门B柱铰链.png']),
            new Metadata('reason_33', '右前纵梁', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/33右前纵梁.png']),
            new Metadata('reason_37', '左前纵梁', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/37左前纵梁.png']),
            new Metadata('reason_35', '右前上纵梁', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/35右前上纵梁.png']),
            new Metadata('reason_290', '左前上纵梁', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B03左前上纵梁照.png']),


            new Metadata('reason_30', '车辆保单正本', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/03车辆保单正本.png']),
            new Metadata('reason_50', '中央控制面板', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B05中央控制面板.png']),
            new Metadata('reason_100', '机盖', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/09机盖.png']),
            new Metadata('reason_270', '机盖铰链螺丝特写', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B01机盖铰链螺丝特写.png']),
            new Metadata('reason_280', '水箱支架连接螺丝特写', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B02水箱支架连接螺丝特写.png']),
            new Metadata('reason_300', '左侧下边梁照', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B04左侧下边梁照.png']),
            new Metadata('reason_310', 'B柱与门框特写', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B06B柱与门框特写.png']),
            new Metadata('reason_330', 'C柱特写', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B08C柱特写.png']),
            new Metadata('reason_340', '行李箱左侧特写', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B09行李箱左侧特写.png']),
            new Metadata('reason_350', '后围板与底板链接处', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B10行李箱右侧底板.png']),
            new Metadata('reason_360', '右下边梁', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B11右下边梁.png']),
            new Metadata('reason_370', '右后门框特写', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B12右后门框特写.png']),
            new Metadata('reason_380', '柱与门框特写', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/B13B柱与门框特写.png']),

            new Metadata('reason_390', '右后底部纵梁', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/右后底部纵梁.png']),
            new Metadata('reason_400', '右后翼子板导水槽', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/右后翼子板导水槽.png']),
            new Metadata('reason_410', '右前避震器座胶水及焊点', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/右前避震器座胶水及焊点.png']),
            new Metadata('reason_420', '右前上纵梁与避震器链接处', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/右前上纵梁与避震器链接处.png']),
            new Metadata('reason_430', '右前翼子板内衬特写', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/右前翼子板内衬特写.png']),
            new Metadata('reason_440', '左后翼子板导水槽', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/左后翼子板导水槽.png']),
            new Metadata('reason_450', '左前避震器座胶水及焊点', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/左前避震器座胶水及焊点.png']),
            new Metadata('reason_460', '左后底部纵梁', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/左后底部纵梁.png']),
            new Metadata('reason_470', '左前翼子板内衬', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/左前翼子板内衬.png']),

            new Metadata('reason_480', '后排座椅', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/后排座椅.png']),
            new Metadata('reason_490', 'A/B/C柱内饰', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/ABC柱.png']),
            new Metadata('reason_500', '水框全景照片', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'img_sample2/水框全景照片.png']),

            //video
            new Metadata('v1', '整车视频', 'video', [], ['least' => 1, 'most' => 1, 'groups' => '视频', 'sample' => 'video_sample/sample.mp4']),
            new Metadata('append_video', '视频补充', 'video', [], ['least' => 1, 'most' => 1, 'groups' => '补充']),

            // backreason_video
            new Metadata('reason_v1', '整车视频', 'radio', ['正常', '不清晰', '缺少', '其它'], ['appendText' => '附加说明', 'sample' => 'video_sample/sample.mp4']),

        ]);
    }

    protected function initMetadataMap($metadatas)
    {
        foreach ($metadatas as $metadata) {
            $this->metadataMap[$metadata->key] = $metadata;
        }
    }

    protected function registerPictureMetadatas()
    {
        $enabled = ['k1', 'k2', 'k4', 'k5', 'k130', 'k7', 'k135', 'k10', 'k11', 'k12', 'k13', 'k131', 'k15', 'k17', 'k140', 'k19', 'k21', 'k22', 'k23', 'append'];
        $disabled = ['k3','k6', 'k8', 'k9', 'k14', 'k16', 'k18','k20'];
        return [$enabled, $disabled];
    }

    protected function registerVideoMetadatas()
    {
        if($this->video) {
           $enabled = ['v1', 'append_video'];
        } else {
           $enabled = [];
        }    
           $disabled = []; 
        
        return [$enabled, $disabled];
    }


    protected function registerExtraMetadatas()
    {
        $enabled = [];
        $disabled = [];
        return [$enabled, $disabled];
    }

    protected function registerBackreasonMetadatas()
    {
        $enabled = ['reason_1', 'reason_60', 'reason_70', 'reason_150', 'reason_160', 'reason_6', 'reason_7', 'reason_8', 'reason_170',
            'reason_180', 'reason_12', 'reason_190', 'reason_14', 'reason_320', 'reason_16', 'reason_17', 'reason_200', 
            'reason_210', 'reason_220', 'reason_230', 'reason_22', 'reason_90', 'reason_24', 'reason_80', 'reason_26', 'reason_27',
            'reason_240', 'reason_250', 'reason_260', 'reason_120', 'reason_110', 'reason_130',
            'reason_140',  'reason_40', 'reason_20', 'reason_10',
            'reason_11', 'reason_29', 'reason_33', 'reason_37', 'reason_35', 'reason_290',
            'reason_390', 'reason_400', 'reason_410',
            'reason_420', 'reason_430', 'reason_440', 'reason_450', 'reason_460', 'reason_470',
            'reason_480','reason_490','reason_500',
            // 之前disable现在不用disable
            'reason_30', 'reason_50', 'reason_100', 'reason_270', 'reason_280', 'reason_300', 'reason_310', 'reason_330', 'reason_340', 'reason_350', 'reason_360', 'reason_370', 'reason_380',
        ];
        $disabled = ['reason_200', 'reason_230', 'reason_26'];
        // $disabled = ['reason_30', 'reason_50', 'reason_100', 'reason_270', 'reason_280', 'reason_300', 'reason_310', 'reason_330', 'reason_340', 'reason_350', 'reason_360', 'reason_370', 'reason_380'];
        return [$enabled, $disabled];
    }

    //video的退回原因
    protected function registerBackreasonVideoMetadatas()
    {
        if($this->video) {
            $enabled = ['reason_v1'];
        } else {
            $enabled = [];
        }
        $disabled = [];

        return [$enabled, $disabled];
    }

    //后台的退回原因
    protected function registerBackstagereasonMetadatas()
    {
        $enabled = ['reason_1', 'reason_60', 'reason_70', 'reason_150', 'reason_160', 'reason_6', 'reason_7', 'reason_8', 'reason_170',
            'reason_180', 'reason_11', 'reason_12', 'reason_190', 'reason_14', 'reason_320', 'reason_16', 'reason_17', 'reason_200', 
            'reason_210', 'reason_220', 'reason_230', 'reason_22', 'reason_90', 'reason_24', 'reason_80', 'reason_26', 'reason_27',
            'reason_240', 'reason_29', 'reason_250', 'reason_260', 'reason_120', 'reason_33', 'reason_110', 'reason_35', 'reason_130',
            'reason_37', 'reason_140', 'reason_290', 'reason_40','reason_20','reason_10', 'reason_v1',
        ];
        $disabled = [];

        return [$enabled, $disabled];
    }

    public function getMetadata4Order($onlyEnable = true)
    {   
        return $this->getMetadata("picture", $onlyEnable);
    }

    public function getMetadata4OrderVideo($onlyEnable = true)
    {   
        return $this->getMetadata("video", $onlyEnable);
    }

    public function getMetadata4OrderExtra($onlyEnable = true)
    {
        return $this->getMetadata("extra", $onlyEnable);
    }

    public function getMetadata4BackReason($onlyEnable = true)
    {
        return $this->getMetadata("backreason", $onlyEnable);
    }

    public function getMetadata4BackReasonVideo($onlyEnable = true)
    {
        return $this->getMetadata("backreasonvideo", $onlyEnable);
    }

    public function getMetadata4BackstageReason($onlyEnable = true)
    {
        return $this->getMetadata("backstage", $onlyEnable);
    }

    protected function getMetadata($which, $onlyEnable = true)
    {
        $register = "";
        if ($which === "picture") {
            $register = "registerPictureMetadatas";
        }
        else if ($which === "video") {
            $register = "registerVideoMetadatas";
        }
        else if ($which === "extra") {
            $register = "registerExtraMetadatas";
        }
        else if ($which === "backreasonvideo") {
            $register = "registerBackreasonVideoMetadatas";
        }
        else if ($which === "backstage") {
            $register = "registerBackstagereasonMetadatas";
        }
        else{
            $register = "registerBackreasonMetadatas";
        }
        list($enabled, $disabled) = $this->$register();
        $ret = [];
        foreach ($enabled as $key) {
            $ret[] = $this->metadataMap[$key];
        }
        if ($onlyEnable) {
            return $ret;
        }
        foreach ($disabled as $key) {
            $metadata = $this->metadataMap[$key];
            $metadata->enable = false;
            $ret[] = $metadata;
        }
        return $ret;
    }

    /**
     * 事故点
     */
    protected function getAccidents()
    {
        return [
            'accident1' => '前保险杠',
            'accident2' => '左前大灯',
            'accident3' => '右前大灯',
            'accident4' => '引擎盖',
            'accident5' => '挡风玻璃',
            'accident6' => '车顶',
            'accident7' => '后挡风玻璃',
            'accident8' => '后备箱盖',
            'accident9' => '后保险杠',
            'accident10' => '左前叶子板',
            'accident11' => '右前叶子板',
            'accident12' => '左前门',
            'accident13' => '右前门',
            'accident14' => '左后门',
            'accident15' => '右后门',
            'accident16' => '左后叶子板',
            'accident17' => '右后叶子板',
        ];
    }

    public function getGroups()
    {
        return array('证件照', '车型图', '附加');
    }

    public function getVideoGroups()
    {
        return array('整车视频');
    }


    //app 新建订单按照组顺序排列
    public function getGroupsForApp()
    {
        return array('车型图', '证件照', '附加');
    }

    //app 新建订单按照组顺序排列,因新增视频重新构建groups
    public function getNewGroupsForApp()
    {
        return array('车型图', '证件照', '视频', '附加', '补充');
    }

    public function getMetadata4CheckStep1()
    {
        // 'required' => false 主要用于后端的非空校验
        $metadatas = [
            new Metadata('field_1010', '牌照号码', 'text'),
            new Metadata('field_1020', '使用性质', 'radio', ['营运', '非营运', '营转非', '其它'], ['appendText' => self::PLACEHOLDER]),
            new Metadata('field_1021', '是否平行进口', 'checkbox', ['是'], ['required' => false]),
            new Metadata('field_1030', '厂牌型号', 'text'),
            new Metadata('field_1040', 'VIN', 'text',[],['class' => 'upper']),
            new Metadata('field_1050', '发动机号', 'text',[],['class' => 'upper']),

            new Metadata('field_3070', '燃油类型', 'radio', ['汽油', '柴油', '油电混合', '电力', '插电式混动', '其它'], ['appendText' => self::PLACEHOLDER, 'numRows' => 1]),
            new Metadata('field_3030', '车身颜色', 'radio', ['黑色', '灰色', '白色', '棕色', '蓝色', '红色', '橙色', '黄色', '绿色', '金色', '银色', '紫色', '米色', '香槟色', '巧克力色', '其它'], ['appendText' => self::PLACEHOLDER,'numRows' => 1]),
            new Metadata('field_3020', '排量(L)', 'text'),
            new Metadata('field_3080', '功率(KW)', 'text'),
            new Metadata('field_3050', '座位数', 'radio', ['5', '7', '其它'], ['appendText' => self::PLACEHOLDER]),
            new Metadata('field_3040', '出厂日期', 'date'),

            new Metadata('field_1060', '注册日期', 'date', null, ['已注册', '未注册']),
            new Metadata('field_1070', '年审有效期', 'date'),
            new Metadata('field_1080', '过户次数', 'radio', [0, 1, 2, 3, '其它'], ['appendText' => self::PLACEHOLDER, 'appendTextarea' => ['title' => '过户详细记录']]),
        ];
        return $metadatas;
    }

    public function getMetadata4CheckStep2()
    {
        $metadatas = [
            new Metadata('field_2010', '品牌', 'text'),
            new Metadata('field_2020', '车系', 'text'),
            new Metadata('field_2030', '车型', 'text'),
            new Metadata('field_2040', '年款', 'text'),

            // 隐藏字段，加readonly是为了跳过前端最后提交时非空的验证
            new Metadata('field_2011', '品牌id', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_2021', '车系id', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_2031', '车型id', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_2041', '竞价次数', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_2051', '平均价', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_2061', '历史拍卖价格', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_2071', '事故图片', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_2081', '新车指导价', 'text', [], ['readonly' => true, 'required' => false]),
        ];
        return $metadatas;
    }

    public function getMetadata4CheckStep3()
    {
        $metadatas = [

            // 基本信息
            new Metadata('field_3010', '表显里程(km)', 'text', [], ['placeholder' => '单位：公里','class' => 'number']),
            new Metadata('field_3060', '车辆类型', 'radio', ['轿车', '轿跑车', '客车', '旅行车', '跑车', '皮卡', '越野车', '敞篷轿跑车', 'MPV', 'SUV', '微面', '轻卡', '其它'], ['appendText' => self::PLACEHOLDER, 'numRows' => 1]),
            // 车辆配置
            new Metadata('field_3090', '环保标准', 'radio', ['国5', '国4', '国3', '国2', '国1', '国标', '黄标', '其它'], ['appendText' => self::PLACEHOLDER, 'numRows' => 1]),
            new Metadata('field_3100', '变速形式', 'radio', ['MT', 'AT', 'AMT', 'CVT', '双离合', '电子换挡', '拨片换挡', '其它'], ['appendText' => self::PLACEHOLDER, 'numRows' => 1]),
            new Metadata('field_3110', '车门数', 'radio', ['五门', '四门', '三门', '两门', '其它'], ['appendText' => self::PLACEHOLDER, 'numRows' => 1]),
            new Metadata('field_3120', '驱动形式', 'radio', ['前轮驱动', '后轮驱动', '四轮驱动', '其它'], ['appendText' => self::PLACEHOLDER]),
            new Metadata('field_3130', '进气方式', 'radio', ['自然吸气', '涡轮增压', '机械增压', '双涡轮增压', '其它'], ['appendText' => self::PLACEHOLDER]),
            new Metadata('field_3140', '天窗', 'radio', ['无', '普通', '全景', '其它'], ['appendText' => self::PLACEHOLDER, 'numRows' => 1]),
            new Metadata('field_3150', '座椅', 'checkbox', ['织物', '真皮', '手动', '主驾电动', '副驾电动', '后排电动', '前排加热', '后排加热', '记忆', '通风', '按摩']),
            new Metadata('field_3155', '空调', 'checkbox', ['手动', '自动', '后排独立空调', '无'], ['required' => false, 'numRows' => 1]),

            new Metadata('field_3160', '后排液晶显示器', 'radio', ['有', '无']),
            new Metadata('field_3170', '巡航', 'radio', ['有', '无']),
            new Metadata('field_3180', '空气悬架', 'radio', ['有', '无']),
            new Metadata('field_3190', '底盘升降', 'radio', ['有', '无']),
            new Metadata('field_3200', '自动大灯', 'radio', ['有', '无']),
            new Metadata('field_3210', '自动雨刮', 'radio', ['有', '无']),
            new Metadata('field_3220', '启动方式', 'radio', ['普通钥匙启动', '无钥匙启动', '其它'], ['appendText' => self::PLACEHOLDER]),
        ];
        return $metadatas;
    }

    public function getMetadata4CheckStep4()
    {
        $metadatas = [
            // 头部，ratio -1为事故车,isAdd 模块是否可叠加 true可叠加
            new Metadata('field_7010', '前横梁（内杠）', 'radio', ['轻度损伤'], ['isAdd' => false, 'ratio' => [0], 'groups' => '头部', 'remark' => ['前横梁轻度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7020', '水箱框架', 'radio', ['轻度损伤', '中度损伤'], ['isAdd' => false, 'ratio' => [5, 10], 'groups' => '头部', 'remark' => ['前横梁轻度损伤', '水箱框架中度损伤，需切割更换'], 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7030', '左右灯架', 'radio', ['轻度损伤', '中度损伤'], ['isAdd' => false, 'ratio' => [5, 10], 'groups' => '头部', 'remark' => ['左右灯架轻度损伤，需拆卸更换', '左右灯架中度损伤，需切割更换'], 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7040', '左右上纵梁', 'radio', ['轻度损伤', '中度损伤', '重度损伤'], ['isAdd' => false, 'ratio' => [10, 15, -1], 'groups' => '头部', 'remark' => ['左右上纵梁轻度损伤，伤到翼子板内板前端', '左右上纵梁中度损伤，伤到翼子板内板', '左右上纵梁轻度损伤，伤到前避震座'], 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7050', '前纵梁', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '头部', 'remark' => ['前纵梁重度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7060', '前避震座', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '头部', 'remark' => ['前避震座重度损伤'], 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7070', '防火墙', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '头部', 'remark' => ['防火墙重度损伤'], 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7080', 'A柱', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '头部', 'remark' => ['A柱重度损伤'], 'required' => false, 'onlyExamer' => true]),

            //侧部
            new Metadata('field_7110', 'A/B/C/D柱', 'radio', ['轻度损伤', '中度损伤', '重度损伤'], ['isAdd' => true, 'ratio' => [0, 5, -1], 'groups' => '侧部', 'remark' => ['A/B/C/D柱轻度损伤，曾喷漆修复', 'A/B/C/D柱中度损伤，曾钣金修复', 'A/B/C/D柱重度损伤，曾切割修复'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7120', '上下边梁', 'radio', ['轻度损伤', '中度损伤', '重度损伤'], ['isAdd' => true, 'ratio' => [0, 5, -1], 'groups' => '侧部', 'remark' => ['上下边梁轻度损伤，曾喷漆修复', '上下边梁中度损伤，曾钣金修复', '上下边梁重度损伤，曾切割修复'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7130', '避震座', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '侧部', 'remark' => ['避震座重度损伤'] , 'required' => false, 'onlyExamer' => true]),

            //尾部
            new Metadata('field_7210', '后横梁（内杠）', 'radio', ['轻度损伤'], ['isAdd' => false, 'ratio' => [0], 'groups' => '尾部', 'remark' => ['后横梁轻度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7220', '后围板', 'radio', ['中度损伤', '重度损伤'], ['isAdd' => false, 'ratio' => [10, -1], 'groups' => '尾部', 'remark' => ['后围板中度损伤', '后围板重度损伤，曾切割更换'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7230', '左右尾灯架', 'radio', ['中度损伤'], ['isAdd' => false, 'ratio' => [10], 'groups' => '尾部', 'remark' => ['左右尾灯架中度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7240', '左右雨水槽', 'radio', ['中度损伤'], ['isAdd' => false, 'ratio' => [10], 'groups' => '尾部', 'remark' => ['左右雨水槽中度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7250', '底板（备胎框）', 'radio', ['中度损伤'], ['isAdd' => false, 'ratio' => [15], 'groups' => '尾部', 'remark' => ['底板中度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7260', '后纵梁', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '尾部', 'remark' => ['后纵梁重度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7270', '行李箱地板', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '尾部', 'remark' => ['行李箱地板重度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7280', '后避震座', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '尾部', 'remark' => ['后避震座重度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7290', '后窗台', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '尾部', 'remark' => ['后窗台重度损伤'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7300', 'C/D柱', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '尾部', 'remark' => ['C/D柱重度损伤，曾切割修复'] , 'required' => false, 'onlyExamer' => true]),

            //顶部
            new Metadata('field_7310', '车顶', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '顶部', 'remark' => ['车顶切割焊补痕迹'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7320', '车体', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '顶部', 'remark' => ['车体整体更换车辆外壳'] , 'required' => false, 'onlyExamer' => true]),

            //底部
            new Metadata('field_7410', '副车架', 'radio', ['中度损伤'], ['isAdd' => false, 'ratio' => [5], 'groups' => '底部', 'remark' => ['副车架中度损伤，曾更换'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7420', '发动机油底壳', 'radio', ['中度损伤'], ['isAdd' => false, 'ratio' => [5], 'groups' => '底部', 'remark' => ['发动机油底壳中度损伤，曾更换'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7430', '变速箱油底壳', 'radio', ['中度损伤'], ['isAdd' => false, 'ratio' => [5], 'groups' => '底部', 'remark' => ['变速箱油底壳中度损伤，曾更换'] , 'required' => false, 'onlyExamer' => true]),

            //机械装置
            new Metadata('field_7510', '发动机', 'radio', ['轻度损伤', '中度损伤'], ['isAdd' => false, 'ratio' => [5, 10], 'groups' => '机械装置', 'remark' => ['发动机曾更换', '发动机曾大修'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7520', '变速箱', 'radio', ['轻度损伤', '中度损伤'], ['isAdd' => false, 'ratio' => [5, 10], 'groups' => '机械装置', 'remark' => ['变速箱曾更换', '变速箱曾大修'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7530', '安全带', 'radio', ['中度损伤'], ['isAdd' => false, 'ratio' => [10], 'groups' => '机械装置', 'remark' => ['安全带曾更换'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7540', '单侧气囊', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '机械装置', 'remark' => ['单侧气囊曾气帘爆掉'] , 'required' => false, 'onlyExamer' => true]),
            new Metadata('field_7550', '主副气囊', 'radio', ['重度损伤'], ['isAdd' => false, 'ratio' => [-1], 'groups' => '机械装置', 'remark' => ['主副气囊曾爆掉'] , 'required' => false, 'onlyExamer' => true]),

            new Metadata('field_4140', '特殊车况', 'checkbox', ['水泡车', '火烧车', '事故车'], ['required' => false, 'onlyExamer' => true]),
            new Metadata('field_4150', '车况总结', 'textarea', [], ['onlyExamer' => true]),
            new Metadata('field_result', '评估结果', 'radio', ['评估通过', '拒绝放贷'], ['appendTextarea' => ['placeholder' => '若拒绝放贷，请在这里填写拒绝的理由'], 'onlyExamer' => true]),
        ];
        return $metadatas;
    }

    public function getMetadata4CheckStep5()
    {
        $metadatas = [
            new Metadata('field_4010', '收购价', 'text', [], ['readonly' => true, 'html5input' => 'text','class' => 'number']),
            new Metadata('field_4012', '销售价', 'text', [], ['readonly' => true, 'html5input' => 'text','class' => 'number']),

            new Metadata('field_5010', '同标价(收购价)', 'text', [], ['class' => 'number blur']),
            new Metadata('field_5011', '同标价(销售价)', 'text', [], ['class' => 'number blur']),
            new Metadata('field_5012', '车况价(-)', 'text', [], ['class' => 'number blur allow-empty', 'required' => false]),
            new Metadata('field_5013', '选配价(+)', 'text', [], ['class' => 'number blur allow-empty', 'required' => false]),
            new Metadata('field_5014', '第三方价', 'text', [], ['class' => 'blur allow-empty positive-or-minus', 'required' => false]),

            new Metadata('field_4014', '未来价格', 'text', [], ['html5input' => 'text','class' => 'number', 'required' => false]),

            new Metadata('field_4020', '手工调整指导价(元)', 'text', [], ['html5input' => 'text','class' => 'number']),
            new Metadata('field_4030', '购置税', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_4040', '购入价', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_4050', '成新率', 'text', [], ['unit' => '%', 'class' => 'number percent totle', 'required' => false]),
            new Metadata('field_4060', '车龄调整', 'text', [], ['readonly' => true, 'required' => false]),
            new Metadata('field_4070', '市场冷热', 'text', [], ['unit' => '%', 'class' => 'number percent totle', 'required' => false]),
            new Metadata('field_4080', '更新换代', 'text', [], ['unit' => '%', 'class' => 'number percent totle', 'required' => false]),
            new Metadata('field_4090', '车辆版本系数', 'text', [], ['unit' => '%', 'class' => 'number percent totle', 'required' => false]),
            new Metadata('field_4100', '公里系数', 'text', [], ['unit' => '%', 'class' => 'number percent totle', 'required' => false]),
            new Metadata('field_4110', '车况等级', 'text', [], ['unit' => '%', 'class' => 'number percent totle', 'required' => false]),
            new Metadata('field_4120', '颜色系数', 'text', [], ['unit' => '%', 'class' => 'number percent totle', 'required' => false]),
            new Metadata('field_4130', '整修费', 'text', [], ['html5input' => 'text', 'class' => 'number totle', 'defaultValue' => 0, 'required' => false]),
        ];
        return $metadatas;
    }


    public function getMetadata4CheckArray()
    {
        return [
            $this->getMetadata4CheckStep1(),
            $this->getMetadata4CheckStep2(),
            $this->getMetadata4CheckStep3(),
            $this->getMetadata4CheckStep4(),
            $this->getMetadata4CheckStep5(),
        ];
    }

    /**
     * 用来保存平安回传给我们的数据meta，不做展示，只做接口用
     */
    public function getMetadata4Pingan()
    {
        $metadatas = [
            new Metadata('field_4012', '销售价', 'text', [], ['html5input' => 'text','class' => 'number']),
            new Metadata('field_result', '评估结果', 'radio', ['评估通过', '拒绝放贷'], ['appendTextarea' => ['placeholder' => '若拒绝放贷，请在这里填写拒绝的理由'], 'onlyExamer' => true]),

        ];

        return $metadatas;
    }

    public function buildValue($posts, $metadatas)
    {
        $ret = [];
        foreach ($metadatas as $metadata) {
            if (!isset($posts[$metadata->key])) {
                //throw new \Exception("missing key $metadata->key in posts.");
                continue;
            }
            $ret[$metadata->key] = $metadata->makeValue($posts[$metadata->key]);
        }
        return $ret;
    }

    public function buildDom($metadatas, $reportdata = [])
    {
        $ret = [];
        foreach ($metadatas as $meta) {
            $ret[$meta->key] = $meta->makeDom($reportdata);
        }
        return $ret;
    }

    /**
    * 为app 客户端提供 一张图片，已提交和已退回列表位置。使用：车辆左前45度照片,只返k2
    * 根据getMetadata4Order获得 
    */
    public function getMainPictureKey()
    {
        return 'k2';
    }

    public function pictureRequireMetadata()
    {
        $ret = [
        'k1'=>['display'=>'登记证','require'=>"登记证第1、2页必须包含：\n注册登记摘要信息栏内所有信息、转移登记摘要信息栏内所有信息、注册登记机动车信息栏内所有信息。\n登记证第1、2页必须在同一照片内，证件上的所有信息必须清晰可见。\n登记证其他页中，所有信息必须清晰可见。",'mask'=>'00照片蒙版-车辆产证.png','todo'=>'','tips'=>'02车辆产证.png'],
        'k2'=>['display'=>'车辆左前45度','require'=>"红色标记必须拍入照片",'mask'=>'01照片蒙版-左前45度-标注.png','todo'=>'请打开天窗','tips'=>'05左前45度.png'],
        'k4'=>['display'=>'左前门','require'=>"红色标记必须拍入照片", 'mask'=>'03照片蒙版-左前门-标注.png','todo'=>'','tips'=>'14左前门.png'],
        'k5'=>['display'=>'仪表盘','require'=>"红色标记必须拍入照片 \n总里程清晰",'mask'=>'仪表盘-标注.png','todo'=>'请启动车辆','tips'=>'仪表盘.png'],
        'k130' => ['display'=>'后排座椅', 'require'=>'红色标记必须拍入照片', 'mask'=>'后排座椅-标注.png', 'todo'=>'', 'tips'=>'后排座椅.png'],
        'k7'=>['display'=>'中控台','require'=>"红色标记必须拍入照片 \n中控按键清晰",'mask'=>'06照片蒙版-中控台-标注.png','todo'=>'','tips'=>'18中控台.png'],
        'k135' => ['display'=>'A/B/C柱内饰', 'require'=>'红色标记必须拍入照片', 'mask'=>'ABC柱-标注.png', 'todo'=>'', 'tips'=>'ABC柱.png'],
        'k10'=>['display'=>'左后盖铰链','require'=>"红色标记必须拍入照片",'mask'=>'左后翼子板导水槽-标注.png','todo'=>'','tips'=>'左后翼子板导水槽.png'],
        'k11'=>['display'=>'左后底板','require'=>"红色标记必须拍入照片 \n有盖板的拿掉拍摄",'mask'=>'10照片蒙版-左后底板-标注.png','todo'=>'','tips'=>'20左后纵梁备胎框.png'],
        'k12'=>['display'=>'右后底板','require'=>"红色标记必须拍入照片 \n有盖板的拿掉拍摄",'mask'=>'11照片蒙版-右后底板-标注.png','todo'=>'','tips'=>'21右后纵梁备胎框.png'],
        'k13'=>['display'=>'右后盖铰链','require'=>"红色标记必须拍入照片",'mask'=>'右后翼子板导水槽-标注.png','todo'=>'','tips'=>'右后翼子板导水槽.png'],
        'k131' => ['display'=>'后围板','require'=>"红色标记必须拍入照片",'mask'=>'行李箱右侧底板.png','todo'=>'','tips'=>'B10行李箱右侧底板.png'],
        'k15'=>['display'=>'车辆右后45度','require'=>"红色标记必须拍入照片",'mask'=>'14照片蒙版-右后45度-标注.png','todo'=>'','tips'=>'07右后45度.png'],
        'k17'=>['display'=>'右前门','require'=>"红色标记必须拍入照片",'mask'=>'16照片蒙版-右前门-标注.png','todo'=>'','tips'=>'250右前门.png'],
        'k140' => ['display'=>'水框全景照片', 'require'=>"红色标记必须拍入照片 \n有盖板的拿掉拍摄", 'mask'=>'水框全景照片-标注.png', 'todo'=>'', 'tips'=>'水框全景照片.png'],
        'k19'=>['display'=>'右避震器座','require'=>"红色标记必须拍入照片 \n有盖板的拿掉拍摄",'mask'=>'右前避震器座胶水及焊点-标注.png','todo'=>'','tips'=>'右前避震器座胶水及焊点.png'],
        'k21'=>['display'=>'左避震器座','require'=>"红色标记必须拍入照片 \n有盖板的拿掉拍摄",'mask'=>'左前避震器座胶水及焊点-标注.png','todo'=>'','tips'=>'左前避震器座胶水及焊点.png'],
        'k22'=>['display'=>'铭牌','require'=>"必须包含：\n铭牌中所显示的所有信息。\n如因不明原因导致铭牌丢失的，请直接拍摄原铭牌粘贴处照片并备注说明 。",'mask'=>'21蒙版切图-车辆铭牌.png','todo'=>'','tips'=>'21车辆铭牌.png'],
        'k23'=>['display'=>'其他','require'=>'','mask'=>'','todo'=>'','tips'=>''],
        ];
        return $ret;
    }

    // 废弃
    //t1-t6排序固定
    public function getTitleMetadataForApp()
    {
        $ret = [
            ['key'=>'t1','type' =>'imagelist','title'=>'车型图','show'=>true],
            ['key'=>'t2','type' =>'imagelist','title'=>'证件照','show'=>true],
            ['key'=>'t3','type' =>'imagelist','title'=>'附加','show'=>true],
            ['key'=>'t4','type' =>'int','title'=>'估价','show'=>true],
            ['key'=>'t5','type' =>'string','title'=>'编号','show'=>true],
            ['key'=>'t6','type' =>'string','title'=>'备注','show'=>true],
            ['key'=>'t7','type' =>'string','title'=>'车况自述','show'=>true],
            
        ];
        return $ret;
    }

    // 废弃
    public function askQuertionMetadata()
    {
        $ret = [
                ["type"=>"radio","key"=>"1","title"=>"车辆的功能键部分（例如转向灯、雨刷、定速巡航多功能按键等等）是否正常","status"=>['是','否'],"value"=>[true,false],"hit"=>"缺陷请详细说明"],
                ["type"=>"radio","key"=>"2","title"=>"车辆的发动机是否正常","status"=>['是','否'],"value"=>[true,false],"hit"=>"缺陷请详细说明"],
                ["type"=>"radio","key"=>"3","title"=>"车辆的变速箱是否正常","status"=>['是','否'],"value"=>[true,false],"hit"=>"缺陷请详细说明"],
                ["type"=>"radio","key"=>"4","title"=>"车辆是否出过重大事故","status"=>['是','否'],"value"=>[false,true],"hit"=>"如果选是，请详细说明"],
                ["type"=>"radio","key"=>"5","title"=>"车辆是否有过进水","status"=>['是','否'],"value"=>[false,true],"hit"=>"如果选是，请详细说明"],
                ["type"=>"radio","key"=>"6","title"=>"车辆是否有火烧痕迹","status"=>['是','否'],"value"=>[false,true],"hit"=>"如果选是，请详细说明"],
                ["type"=>"checkbox","key"=>"7","title"=>"我已承诺上述填写内容的真实性，如果在车辆后续交易检测中存在隐瞒事项，愿意重新对车辆价值进行评估，并承担由此造成的损失。","status"=>['是','否'],"value"=>[false,true],"hit"=>""]

        ];
        return $ret;
    }
}