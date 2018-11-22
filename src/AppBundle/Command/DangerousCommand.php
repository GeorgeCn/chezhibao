<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class DangerousCommand extends ContainerAwareCommand {
    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->setName('app:dangerous');
        $this->setDescription('this command should not be excuted');
        $this->addOption('dangerous', "d", InputOption::VALUE_NONE, 'Set this parameter to execute dangerous command');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('dangerous')){
            throw new \Exception("this command is a dangerous command, please run it in dangerous option.");
        }
        $confirm = $this->getHelper('dialog')->askAndValidate(
            $output,
            "{$this->getName()}\n{$this->getDescription()}\nPlease enter 'CONFIrm' to execute command:\n",
            function($confirm) use($output) {
                if ($confirm != "CONFIrm") {
                    $output->writeln('input error!');
                    exit();
                }
                return $confirm;
            }
        );
    }
}
