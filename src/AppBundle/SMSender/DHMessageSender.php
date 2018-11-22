<?php

namespace AppBundle\SMSender;

class DHMessageSender
{
    private $output;
    private $user;
    private $pwd;

    public function __construct($output) {
        $this->output = $output;
    }

    public function setAuthInfor($user, $pwd){
        $this->user = $user;
        $this->pwd = md5($pwd);
    }

    public function send($content, $receivers) {
        if ($this->output) {
            $this->output->writeln('');
            $this->output->writeln(date("Y/m/d h:i:s A"));
            $this->output->writeln($content);
        }
        $receivers = array_unique(array_filter($receivers));
        $count = count($receivers);

        if ($this->output) {
            $this->output->writeln("To:($count)".join(";", $receivers));
            $this->output->writeln('From: short message sender');
        }

        if (count($receivers) == 0) {
            return 0;
        }

        $params = array(
            'account' => $this->user,
            'password' => $this->pwd,
            'phones' => join(',', $receivers),
            'content' => $content,
            'sign' => "【又一车】"
        );
        $time_start = microtime(true)*1000;

        $c = curl_init();
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($c, CURLOPT_URL, "http://wt.3tong.net/json/sms/Submit");
        curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($c);
        curl_close($c);

        if ($this->output) {
            $this->output->writeln(json_encode($result));
        }

        //$result = $result["sendBatchMessageReturn"];
        $time_elapse = round(microtime(true)*1000 - $time_start);

        if ($this->output) {
            $this->output->writeln("$time_elapse ms elapsed.");
        }

        if ($result < 0) {
            throw new \Exception("send return $result");
        }
        return $result;
    }

}
