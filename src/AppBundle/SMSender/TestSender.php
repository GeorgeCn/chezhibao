<?php

namespace AppBundle\SMSender;

class TestSender
{

    private $output;

    public function __construct($output) {
        $this->output = $output;
    }

    public function send($content, $receivers) {
        if (count($receivers) == 0) {
            return 0;
        }
        $receivers = array_unique(array_filter($receivers));
        $count = count($receivers);
        $this->output->writeln('');
        $this->output->writeln(date("Y/m/d h:i:s A"));
        $this->output->writeln($content);
        $this->output->writeln("To:($count)" . join(";", $receivers));
        $this->output->writeln('From: test sender');
        $time_start = microtime(true)*1000;
        $time_elapse = round(microtime(true)*1000 - $time_start);
        $this->output->writeln("$time_elapse ms elapsed.");
        return 1;
    }

    public function setAuthInfor($user, $pwd)
    {
    }
}
