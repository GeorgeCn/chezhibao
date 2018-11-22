<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class VersionUpdateCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('app:version:update')
            ->setDescription('update revision and asset_version.')
            ->addArgument('revision', InputArgument::OPTIONAL, 'revision')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rootDir = $this->getParameter('kernel.root_dir');
        $yml = "{$rootDir}/config/version.yml";
        $ret = Yaml::parse($yml);
        $revision = $input->getArgument('revision');
        if (!empty($revision)) {
            $ret["parameters"]["revision"] = $revision;
        }
        $ret["parameters"]["asset_version"] = date("YmdHis");
        $ret = Yaml::dump($ret);
        file_put_contents($yml, $ret);
    }
}