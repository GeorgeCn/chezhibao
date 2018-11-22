<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Traits\ContainerAwareTrait;

class OrderAppendCommand extends ContainerAwareCommand
{
	use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('order:append')
            ->setDescription('Submit new order')
            ->addOption('domain', 'd', InputOption::VALUE_OPTIONAL, 'domain')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'username')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'password')
            ->addArgument('number', InputArgument::REQUIRED, 'The number of the order')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $number = $input->getArgument('number');
        $username = $input->getOption('username') ? $input->getOption('username') : 'admin';
        $password = $input->getOption('password') ? $input->getOption('password') : 'admin';
        $em = $this->getDoctrine()->getManager();
        $config = $this->getRepo('AppBundle:Config')->find($company);
        $type = $config->getNeedVideo();
        $this->append($input->getOption('domain'), $username, $password, $company, $number, $type);
    }

    private function append($domain, $username, $password, $company, $number, $type)
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
			$orderData = 'append=["hpl/FiLuvq6qBYzUQWA4BuBpZ2o6tpLT","hpl/Fi27HEWJQ-ecI-IwGFmeXjkZ-L4S"]&latitude=31.193626723651022&longitude=121.41958780640847';
			if($type) {
				$orderData .= '&append_video=["test\/FjJTy2LUSKbehnmPk1mS1gNQKrXl"]'; 
			}
			$url = "http://$domain{$this->getContainer()->get('router')->generate('openapi_order_putorder', [], UrlGeneratorInterface::ABSOLUTE_PATH)}";
			$c = curl_init();
	        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($c, CURLOPT_URL, $url);
	        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        	curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PUT");
	        curl_setopt($c, CURLOPT_POSTFIELDS, $orderData);
	        $return = curl_exec($c);
	        curl_close($c);
        } else {
        	echo $objRet->errmsg;
        }
    }
}
