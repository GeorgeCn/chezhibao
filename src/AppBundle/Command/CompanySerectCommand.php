<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompanySerectCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('company-serect:create')
            ->setDescription('修补公司配置表serect的值')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $configs = $em->getRepository('AppBundle:Config')->findall();

        $output->writeln('Total '.count($configs).' records find');
        foreach ($configs as $k => $config) {
            $key = md5($config->getCompany());
            $serect = md5($config->getCompany().rand(1000,2000));
            $config->setCompanyKey($key);
            $config->setCompanySerect($serect);
            $em->persist($config);
            $em->flush();
        }
    }
}