<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;
use AppBundle\Entity\Agency;
use AppBundle\Entity\AgencyRel;

class FixAgencyCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('fix:agency')
            ->setDescription('fix agency, agencyRel')
        ;
    }

    public $agencies = [
        "SH006" => "东莞市鼎和汽车服务有限公司",
        "SH008" => "深圳市乾丰联合汽车服务有限公司",
        "SH020" => "黑龙江常发商务代理服务有限公司",
        "SH021" => "云南融易通汽车服务有限公司",
        "SH022" => "东莞市鼎信汽车服务有限公司",
        "SH026" => "云南厚盈经济信息咨询有限公司",
        "SH031" => "梅州交融汽车信息咨询服务有限公司",
        "SH035" => "成都劲翔汽车销售服务有限公司",
        "SH036" => "贵州隆茂行汽车服务有限公司",
        "SH038" => "云南车享易汽车服务有限公司",
        "SH050" => "福州市二七三汽车经纪有限公司重庆分",
        "SH080" => "鄂尔多斯市诚兴汽车销售服务有限公司",
        "SH081" => "云南车讯贷汽车信息咨询有限公司",
        "SH083" => "山西车易通汽车服务有限公司",
        "SH095" => "恩施科腾贝儿汽车服务有限公司",
        "SH109" => "黑龙江常发商务代理服务有限公司内蒙分公司",
        "SH110" => "呼伦贝尔市汇通诚信汽车租赁有限公司",
        "SH115" => "吉林市世勋阳光汽车销售有限公司",
        "SH118" => "重庆银茂汽车销售服务有限公司",
        "SH119" => "贵州津港汽车贸易有限责任公司",
        "SH130" => "成都和信通诚汽车服务有限公司",
        "SH134" => "先锋太盟内蒙分公司",
        "SH136" => "深圳市铂盾华宇汽车服务有限公司",
        "SH138" => "包头市易通加乘咨询有限责任公司",
        "SH139" => "河源市易诚汽车信息咨询服务有限公司",
        "SH144" => "长春市君临汽车销售服务有限公司",
        "SH149" => "鄂尔多斯市泰盟伟业汽车服务有限责任公司",
        "SH151" => "大连沃银利往经济信息咨询有限公司",
        "SH156" => "四川睿择商务服务有限公司",
        "SH157" => "贵州万马众旺汽车咨询服务有限公司",
        "SH158" => "黑龙江沃融汽车销售有限公司",
        "SH159" => "云南快利经济信息咨询有限公司",
        "SH164" => "云南荣冠实业有限公司",
        "SH168" => "哈尔滨明智宇汽车销售有限公司",
        "SH171" => "四川省华美鑫金融外包服务有限公司",
        "SH172" => "贵州弘正丰汽车销售服务有限公司",
        "SH173" => "西昌利方汽车销售服务有限责任公司",
        "SH175" => "重庆九淇商务信息咨询有限公司",
        "SH181" => "北京阳光第一车网科技有限公司贵州分公司",
        "SH191" => "北京阳光第一车网科技有限公司重庆分公司",
        "sh196" => "桂林兴融汽车服务有限公司",
        "SH197" => "贵州瑞合行汽车服务有限公司",
        "SH200" => "重庆缔信汽车服务有限公司",
        "SH205" => "内蒙古佳融汽车销售有限公司",
        "SH206" => "包头市隆茂达汽车销售服务有限公司",
        "SH207" => "哈尔滨大唐伟业汽车销售服务有限公司",
        "SH209" => "成都巴客汽车服务有限公司",
        "SH212" => "重庆菲冠汽车销售服务有限公司",
        "SH225" => "珠海市金桥汽车服务有限公司",
        "SH228" => "云浮市众汇商务咨询服务有限公司",
        "SH233" => "山西隆鑫鼎义汽车销售服务有限公司",
        "SH235" => "成都巴客汽车服务有限公司云南分公司",
        "SH238" => "广东泰泽汽车贸易有限公司",
        "SH239" => "广州优融汽车销售服务有限公司",
        "SH240" => "云南雨九汽车服务有限公司",
        "SH242" => "云南景双汽车销售有限公司",
        "SH247" => "阳江市科易得汽车服务有限公司",
        "SH249" => "贵州耀成汽车销售服务有限公司",
        "SH250" => "吉林延边车代无忧汽车销售服务有限公司",
        "SH252" => "内蒙古景铄贸易有限责任公司",
        "sh253" => "贵州众诚汽车服务有限公司",
        "SH254" => "四川华敬富汽车销售服务有限公司",
        "SH258" => "松原市鸿远二手车交易有限公司",
        "SH262" => "广西南宁讯策商务服务有限公司",
        "SH265" => "重庆渝钻汽车信息咨询服务有限公司",
        "SH266" => "四川垒立企业管理咨询有限公司",
        "SH270" => "山西汇晶汽车服务有限公司",
        "SH272" => "云南瑞合福德汽车服务有限公司",
        "SH275" => "内蒙古佳融汽车销售有限公司山西分公司",
        "sh281" => "惠州市长信汽车贸易有限公司",
        "SH282" => "云南胤乾汽车销售有限公司",
        "SH283" => "辽宁车融汽车租赁有限公司",
        "SH285" => "云南易汇融汽车销售有限公司",
        "SH288" => "云南麦林汽车销售有限公司",
        "SH291" => "云南恒汇经济信息咨询有限公司",
        "SH295" => "贵州鼎鑫盛汽车服务有限公司",
        "SH296" => "广西四泰合众商贸有限公司",
        "SH297" => "成都神车网络技术有限公司昆明分公司",
        "SH300" => "云南金拓投资有限公司",
        "SH301" => "云南金拓投资有限公司贵州分",
        "SH303" => "北京极致车网科技有限公司广州分公司",
        "SH307" => "北京极致车网科技有限公司重庆分公司",
        "SH308" => "云南融行汽车销售服务有限公司",
        "SH311" => "贵州佰诚汽车咨询服务有限公司",
        "SH316" => "乌鲁木齐快易宝信息咨询服务有限公司",
        "SH317" => "南充恒华商务服务有限公司",
        "SH318" => "辽源市鸿远汽车贸易有限责任公司",
        "SH319" => "成都驰锋汽车销售有限公司",
        "SH320" => "成都劲翔汽车销售服务有限公司贵州分公司",
        "SH321" => "广西汇吉邦信息咨询有限公司-肇庆分",
        "SH325" => "内蒙古太浦汽车销售有限公司",
        "SH326" => "深圳市银利汽车销售服务有限公司",
        "SH328" => "肇东市佰仟隆世汽车销售有限公司",
        "SH330" => "东莞市正汇汽车销售有限公司",
        "SH331" => "青海宇振汽车销售服务有限公司",
        "SH338" => "成都神车网络技术有限公司广西分公司",
        "SH340" => "四川中泰汇祥汽车服务有限公司",
        "SH342" => "贵州锋翔汽车贸易有限公司",
        "SH343" => "保山易通行汽车服务有限公司",
        "SH345" => "韶关市弘晟铭德汽车贸易有限公司",
        "SH349" => "甘肃融利汽车信息咨询服务有限责任公司",
        "SH355" => "东莞市伟程汽车销售有限公司",
        "SH364" => "贵州立宇诚汽车信息咨询服务有限公司",
        "SH394" => "贵州汇通金源投资咨询有限公司",
        "SH402" => "重庆路凡汽车销售有限公司",
        "SH403" => "广西隆隆达汽车服务有限公司",
        "SH407" => "内蒙古佳融汽车销售有限公司吉林分公司",
        "SH410" => "贵州众合鑫盛世商贸有限公司",
        "SH418" => "七台河市信诚商贸有限责任公司",
        "SH423" => "长春市鸿远易车汽车贸易有限公司辽源分公司",
        "SH425" => "内蒙古佳融汽车销售有限公司黑龙江分公司",
    ];

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $this->getParameter("kernel.root_dir")."/data/user.csv";
        $row = 1;
        if (($handle = fopen($file, "r")) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                if ($row === 1) {
                    $row++;
                    continue;
                }

                $id = $data[0];
                $companyName = $data[1]; //mb_convert_encoding($data[1], "UTF-8", "GBK");
                $code = $data[2]; //mb_convert_encoding($data[2], "UTF-8", "GBK");

                $this->handleAgency($id, $companyName, $code, $output);
                $this->getDoctrineManager()->clear();
            }
            fclose($handle);
        }
    }

    public function handleAgency($userId, $companyName, $agencyCode, OutputInterface $output)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getRepo('AppBundle:User')->find($userId);
        $province = $user->getProvince();
        $city = $user->getCity();
        $agencyName = $agencyCode;
        $company = $this->getRepo('AppBundle:Config')->findOneBy(['company' => $companyName]);
        if ($company) {
            $admin = $this->getRepo('AppBundle:User')->find(1);
            $roles = $user->getRoles();
            $type = 0;
            if (in_array('ROLE_ADMIN', $roles) || in_array('ROLE_EXAMER_HPL', $roles) || in_array('ROLE_ADMIN_HPL',  $roles) || in_array('ROLE_EXAMER',  $roles) || in_array('ROLE_EXAMER_MANAGER',  $roles)) {
                $agencyCode = 'admin';
                $agencyName = '管理员';
            }
            elseif ($companyName == "先锋太盟" && in_array($agencyCode, array_keys($this->agencies))) {
                $agencyName = $this->agencies[$agencyCode];
            }

            $agency = $this->getRepo('AppBundle:Agency')->findOneBy(['code' => $agencyCode, 'company' => $company]);
            if (!$agency) {
                $agency = new Agency();
                $agency->setCompany($company)
                    ->setName($agencyName)
                    ->setProvince($province)
                    ->setCity($city)
                    ->setCode($agencyCode)
                    ->setCreater($admin)
                ;

                $em->persist($agency);
                $em->flush();
            }

            $agencyRel = $this->getRepo('AppBundle:AgencyRel')->findOneBy(['user' => $user, 'agency' => $agency]);
            if (!$agencyRel) {
                $agencyRel = new AgencyRel();
                $agencyRel->setCompany($company)
                    ->setUser($user)
                    ->setAgency($agency)
                    ->setCreater($admin);
                ;

                $em->persist($agencyRel);
                $em->flush();
            }

            $output->writeln('agencyId:'.$agency->getId().' handle successfuly!');
        }
        else{
            $output->writeln("warning: {$user->getName()} {$companyName}, {$agencyCode}");
    }
    }
}