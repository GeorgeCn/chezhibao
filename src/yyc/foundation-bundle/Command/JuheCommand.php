<?php

namespace YYC\FoundationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JuheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('YYCFoundation:juhe')
            ->setDescription('集合数据辅助命令')
            ->addArgument('cmd', InputArgument::REQUIRED, '子命令, configurl, notify')
            ->addOption('domain', 'd', InputOption::VALUE_OPTIONAL, 'domain')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cmd = $input->getArgument('cmd');
        if ($cmd == "configurl") {
            $this->configUrl($input->getOption('domain'));
        }
        else if ($cmd == "notify"){
            $this->notify($input->getOption('domain'));
        }
    }

    private function configUrl($domain)
    {
        $url = "http://$domain{$this->getContainer()->get('router')->generate('yyc_juhe_notify', [], UrlGeneratorInterface::ABSOLUTE_PATH)}";
        $ret = $this->getContainer()->get("yyc_foundation.third.juhe")->configUrl($url);
    }

    private function notify($domain)
    {
        $url = "http://$domain{$this->getContainer()->get('router')->generate('yyc_juhe_notify', [], UrlGeneratorInterface::ABSOLUTE_PATH)}";
        $data = $this->getContainer()->get("yyc_foundation.third.juhe")->getMaintenanceDetail("", "");
        $c = curl_init();
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($c, CURLOPT_POST, 1);
        curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($data));
        $ret = curl_exec($c);
        curl_close($c);
    }
}
