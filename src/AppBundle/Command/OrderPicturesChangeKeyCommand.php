<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;

/**
 * metadata 统一后，修改之前的图片key(针对 客服创建，成都建国汽车 两种公司用户创建的order)
 */
class OrderPicturesChangeKeyCommand extends DangerousCommand
{
    use ContainerAwareTrait;

    public function configure()
    {
        parent::configure();
        $this->setName('app:orderpictureschangekey')
              ->setDescription('update order pictures change key.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $time1 = time();
        $companys = ['成都建国汽车'];
        $type = 4;
        $em = $this->get('doctrine')->getManager();
        /*
        $Users = $em->getRepository('AppBundle:User')->findBy(["company"=>$companys]);
        print_r($Users);
        */
        $em = $this->getDoctrineManager();
        $re_user_j = $em->getRepository('AppBundle:User')
                    ->createQueryBuilder('u')
                    ->where('u.company in (:companys)')
                    ->setParameter('companys', $companys)
                    ->getQuery()
                    ->getResult();
        $re_user_c = $em->getRepository('AppBundle:User')
                    ->createQueryBuilder('u')
                    ->where('u.type = :type')
                    ->setParameter('type', $type)
                    ->getQuery()
                    ->getResult();
        $re_user = array_merge($re_user_j,$re_user_c);


        $qb = $em->getRepository('AppBundle:Order')
                    ->createQueryBuilder('o')
                    ->leftJoin('o.loadOfficer', 'u')
                    ->where('o.loadOfficer in (:users)')
                    ->setParameter('users', $re_user)
                    ->orderBy('o.id', 'DESC');

        $count = $qb->select('COUNT(o.id)')
                        ->getQuery()
                        ->getSingleResult(\Doctrine\ORM\Query::HYDRATE_SINGLE_SCALAR);
        $limit = 100;
        gc_enable();
        for ($i=0; $i<ceil($count/$limit); $i++) {
            $ret = $qb->select('o')
                      ->setFirstResult($i*$limit)
                      ->setMaxResults($limit)
                      ->getQuery()
                      ->getResult();
            foreach ($ret as $order) {
                echo $order->getId()."\n\r";
                $this->changeOrderPictureKey($order);
            }
            $em->clear();
            gc_collect_cycles();

            $memory_usage = memory_get_usage();
            echo "memory_usage：".round($memory_usage/1024/1024, 2)."MB\n";
            $time2 = time()-$time1;
            echo "处理总时间：".$time2."秒\r\n";
        }
    }

    public function changeOrderPictureKey($order)
    {
        $em = $this->getDoctrineManager();
        $pictures = $order->getPictures();
        $picturesMap = $this->metadataMap();
        $newPictures = [];
        if(!isset($pictures['k110']) && count($pictures) > 30 ){
            foreach($pictures as $k=>$v){
                if(strpos($k, 'append') === false){
                    $newk = $picturesMap[$k];
                    $newPictures[$newk] = $v;
                }else{
                    $newPictures[$k] = $v;
                }
            }
            $order->changePicturesKeys($newPictures);
            $em->flush();
        }
        return;
    }

    public function metadataMap()
    {
        return $metadata = [
                'k1'=>'k30',
                'k2'=>'k2',
                'k3'=>'k3',
                'k4'=>'k4',
                'k5'=>'k35',
                'k6'=>'k40',
                'k7'=>'k45',
                'k8'=>'k50',
                'k9'=>'k5',
                'k10'=>'k6',
                'k11'=>'k55',
                'k12'=>'k60',
                'k13'=>'k7',
                'k14'=>'k8',
                'k15'=>'k9',
                'k16'=>'k65',
                'k17'=>'k70',
                'k18'=>'k10',
                'k19'=>'k11',
                'k20'=>'k12',
                'k21'=>'k13',
                'k22'=>'k75',
                'k23'=>'k14',
                'k24'=>'k80',
                'k25'=>'k15',
                'k26'=>'k85',
                'k27'=>'k90',
                'k28'=>'k16',
                'k29'=>'k95',
                'k30'=>'k17',
                'k31'=>'k100',
                'k32'=>'k18',
                'k33'=>'k105',
                'k34'=>'k19',
                'k35'=>'k110',
                'k36'=>'k20',
                'k37'=>'k115',
                'k38'=>'k21',
                'k39'=>'k120',
                'k40'=>'k22',
                'k41'=>'k1',
                'k42'=>'k23',
            ];
    }
}