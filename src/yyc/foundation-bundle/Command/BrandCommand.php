<?php

namespace YYC\FoundationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use YYC\FoundationBundle\Entity\Brand;

/**
 * 用来定期更新数据库中从大圣来了接口获取到的品牌数据信息(每次更新会删除原有的品牌数据)
 */
class BrandCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dsll-brand:update')
            ->setDescription('update the dashenglaile brand info, this will delete the original data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $query = $em->createQuery('delete from YYC\FoundationBundle\Entity\Brand');
        $numDeleted = $query->execute();

        $output->writeln('Total '.$numDeleted.' records deleted:');

        $brandsJson =  $this->getContainer()->get('yyc_foundation.third.dsll')->getBrands();
        $tmp = json_decode($brandsJson);
        $brandsArray = $tmp->response;

        // print_r($brandsArray);exit;

        foreach ($brandsArray as $key => $value) {
            $brand = new Brand();
            $brand->setBrandId($value->brand_id);
            $brand->setName($value->brand_name);
            $brand->setPrice($value->brand_price);
            $brand->setBrandTips($value->brand_tips);
            $brand->setIsNeedEngineNumber($value->is_need_engine_number);
            $em->persist($brand);
            $em->flush();
            $output->writeln('Inserted database brandId: '.$value->brand_id);
        }
    }
}