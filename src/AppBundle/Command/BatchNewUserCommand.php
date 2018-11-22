<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Traits\ContainerAwareTrait;


/**
 * 批量从excel创建用户,如果用户已存在，执行其它字段的更新操作
 */
class BatchNewUserCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this
            ->setName('batch-new-user:create')
            ->setDescription('batch create user from file')
            ->addArgument('fileName', InputArgument::REQUIRED, 'Which file you want to import?')
            ->addOption('excute', "x", InputOption::VALUE_OPTIONAL, 'excute this command');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bExcute = $input->getOption('excute') !== NULL;
        $fileName = $input->getArgument('fileName');
        $file = $this->getParameter("kernel.root_dir")."/data/$fileName";
        $handle = fopen($file, "r");
        if ($handle === FALSE) {
            return;
        }
        $row = 0;
        while (($data = $this->fgetcsv($handle, 1000, ",", '"')) !== FALSE) {
            $row++;
            if ($row == 1) {
                continue;
            }
            $username = $data[0];
            $password = $data[1];
            $name = $data[2];
            $mobile = $data[3];
            $provinceName = $data[4];
            $cityName = $data[5];
            $company = $data[6];
            $companyCode = $data[7];

            $userManager = $this->get('fos_user.user_manager');
            $oldUser = $this->getRepo("AppBundle:User")->findUserByUsernameOrMobile($username);
            if ($oldUser) {
                echo "user {$username} 已存在。\n";
                continue;
            }
            $province = $this->getRepo("AppBundle:Province")->findProvince($provinceName);
            if (empty($province)) {
                echo "user {$username} province {$provinceName} 不存在。\n";
                continue;
            }
            $city = $this->getRepo("AppBundle:City")->findCity($cityName);
            if (empty($city)) {
                echo "user {$username} city {$cityName} 不存在。\n";
                continue;
            }

            if ($bExcute) {
                continue;
            }

            $user = $userManager->createUser();
            $user->setProvince($province);
            $user->setCity($city);
            $user->setUsername($username);
            $user->setPlainPassword($password);
            $user->setName($name);
            $user->setMobile($mobile);
            $user->setCompany($company);
            $user->setCompanyCode($companyCode);
            $user->setRoles(array('ROLE_LOADOFFICER'));

            $userManager->updateUser($user);
            echo "user {$username} created.\n";
        }
    }

    protected function fgetcsv(& $handle, $length = null, $d = ',', $e = '"')
    {
        $d = preg_quote($d);
        $e = preg_quote($e);
        $_line = "";
        $eof=false;
        while ($eof != true) {
         $_line .= (empty ($length) ? fgets($handle) : fgets($handle, $length));
         $itemcnt = preg_match_all('/' . $e . '/', $_line, $dummy);
         if ($itemcnt % 2 == 0)
             $eof = true;
        }
        $_csv_line = preg_replace('/(?: |[ ])?$/', $d, trim($_line));
        $_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';
        preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
        $_csv_data = $_csv_matches[1];
        for ($_csv_i = 0; $_csv_i < count($_csv_data); $_csv_i++) {
         $_csv_data[$_csv_i] = preg_replace('/^' . $e . '(.*)' . $e . '$/s', '$1' , $_csv_data[$_csv_i]);
         $_csv_data[$_csv_i] = str_replace($e . $e, $e, $_csv_data[$_csv_i]);
        }
        return empty ($_line) ? false : $_csv_data;
    }
}