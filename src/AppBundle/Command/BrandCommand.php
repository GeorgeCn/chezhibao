<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\Brand;

/**
 * 用来定期更新数据库中从第一车网接口获取到的品牌数据信息(每次更新会删除原有的品牌数据)
 */
class BrandCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dyc-brand:update')
            ->setDescription('update the diyichewang brand info, this will delete the original data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $query = $em->createQuery('delete from AppBundle\Entity\Brand');
        $numDeleted = $query->execute();

        $output->writeln('Total '.$numDeleted.' records deleted:');

        $brandsJson =  $this->getContainer()->get('app.third.dyc')->getBrands();
        $brandsArray = json_decode($brandsJson);

        foreach ($brandsArray as $key => $value) {
            $brand = new Brand();
            $brand->setBrandId($value->id);
            $brand->setName($value->name);
            $em->persist($brand);
            $em->flush();
            $output->writeln('Inserted database brandId: '.$value->id);
        }
    }
}