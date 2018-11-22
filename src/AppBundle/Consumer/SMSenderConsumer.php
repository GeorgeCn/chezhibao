<?php

namespace AppBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use AppBundle\SMSender\DHMessageSender;
use AppBundle\SMSender\TestSender;

class SMSenderConsumer implements ConsumerInterface
{
    public function setContainer(ContainerInterface $container) {
        $this->container = $container;
        $this->output = new ConsoleOutput();
    }

    public function execute(AMQPMessage $msg){
        $msg = unserialize($msg->body);
        try{
            $ret = $this->handle($msg);
        }
        catch(\Exception $e){
            $ret = $e->getMessage();
            $this->output->writeln("$ret\n");
        }
        return true;
    }

    private function handle($msg){
        $receivers = [];
        $content = "";
        if (isset($msg["custom_content"])) {
            $content = $msg["custom_content"];
            $receivers = $msg["custom_tos"];
        }
        return $this->send($content, $receivers);
    }

    private function send($content, $receivers){
        $message_debug = $this->container->getParameter("message_debug");
        $sender = $message_debug ? new TestSender($this->output) : new DHMessageSender($this->output);
        $sender->setAuthInfor("dh26111", "00?i3vS5");
        return $sender->send($content, $receivers);
    }
}