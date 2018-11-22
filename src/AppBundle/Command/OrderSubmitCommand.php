<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Traits\ContainerAwareTrait;

class OrderSubmitCommand extends ContainerAwareCommand
{
	use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('order:submit')
            ->setDescription('Submit new order')
            ->addOption('domain', 'd', InputOption::VALUE_OPTIONAL, 'domain')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'username')
            ->addOption('mobile', 'm', InputOption::VALUE_OPTIONAL, 'mobile')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'password')
            ->addArgument('company', InputArgument::REQUIRED, 'The companyID of the order')
            ->addArgument('number', InputArgument::REQUIRED, 'The number of the order')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $company = $input->getArgument('company');
        $number = $input->getArgument('number') > 10 ? 10 : $input->getArgument('number');
        $username = $input->getOption('username') ? $input->getOption('username') : 'admin';
        $mobile = $input->getOption('mobile');
        $password = $input->getOption('password') ? $input->getOption('password') : 'admin';
        $em = $this->getDoctrine()->getManager();
        $config = $this->getRepo('AppBundle:Config')->find($company);
        $type = $config->getNeedVideo();
        if($mobile) {
        	$user = $this->getRepo('AppBundle:User')->findOneBy(['mobile' => $mobile]);
        	$username = $user->getUsername();
        }
        $this->submit($input->getOption('domain'), $username, $password, $company, $number, $type); 
    }

    private function submit($domain, $username, $password, $company, $number, $type)
    {
        $url = "http://$domain{$this->getContainer()->get('router')->generate('token_gen', [], UrlGeneratorInterface::ABSOLUTE_PATH)}";
        $userData['system'] = 'hpl';
        $userData['user'] = $username;
        $userData['psw'] = $password;
        $c = curl_init();
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, $userData);
        $ret = curl_exec($c);
        curl_close($c);
        $objRet = json_decode($ret);
        if($objRet->errcode == 0) {
        	$token = 'X-ACCESS-TOKEN:'.$objRet->data->token;
        	$headers[] = 'Content-Type:application/x-www-form-urlencoded';
			$headers[] = $token;
			$orderData = 'companyId='.$company.'&extras={"valuation":"65000","remark":"发动机舱实在是不好拆，希望理解，谢谢。"}&latitude=31.193626723651022&longitude=121.41958780640847&pictures={"k1":["hpl\/FvOD8NfufuIpgN0c3BRxHwKO5JyO","hpl\/FsYN3jchaz_xSYUeDm72_ENhjZfv"],"k2":["hpl\/FuJzJ_iw44Jq1gZ4f6JVjTXlMAFl"],"k4":["hpl\/FloBuoEUVBrG6H6rUbWh6YOTfj3T"],"k5":["hpl\/FvN0deMJBvpekK-zBnul7pmKx8cu"],"k130":["hpl\/FhdJ9evT_VFJI_4hOnaMrwnF0m5w"],"k7":["hpl\/FnOVh7zrwvm7jbrhmScODHXgen6G"],"k135":["hpl\/FhK7umRLhtQpXACcUP-9jlabe11y"],"k10":["hpl\/FmxP3Hh3_N8f45w_6WN6elhDCJWA"],"k11":["hpl\/FnJagJN1v-3mBRRhu0Kb7p4kCWsa"],"k12":["hpl\/FqCK7gY3TvauBFjLGDxZfhHSVSps"],"k13":["hpl\/FrwPKufkZwdYrskmfODgUEFPr0aJ"],"k15":["hpl\/FmwGs2Dwymy8KEdM5OAShPuD3bnN"],"k17":["hpl\/Fsv99NeHm5JpY9cEq56QFEc63ziA"],"k140":["hpl\/FuChreq4dY8G5EdIIY-P294cnDMx"],"k19":["hpl\/FrN4H7j3MwxoL63G49FJpBaItHfE"],"k21":["hpl\/FnKhbKVbIAJh-8LMAFiaweZUOwxJ"],"k22":["hpl\/FnwpGAuTOkaKjuulike0h6CKyC37"]}';
			if($type) {
				$orderData .= '&videos={"v1":["test\/FjJTy2LUSKbehnmPk1mS1gNQKrXl"]}'; 
			}
			$url = "http://$domain{$this->getContainer()->get('router')->generate('openapi_order_postorder', [], UrlGeneratorInterface::ABSOLUTE_PATH)}";
        	for($i=0;$i<$number;$i++) {
				$c = curl_init();
		        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
		        curl_setopt($c, CURLOPT_URL, $url);
		        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
	        	curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
		        curl_setopt($c, CURLOPT_POST, 1);
		        curl_setopt($c, CURLOPT_POSTFIELDS, $orderData);
		        $return = curl_exec($c);
		        curl_close($c);
        	}
        } else {
        	echo $objRet->errmsg;
        }
    }
}
