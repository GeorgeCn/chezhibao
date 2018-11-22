<?php

namespace AppBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JPush\Model as M;
use JPush\JPushClient;
use JPush\Exception\APIRequestException;

class JpushSenderConsumer implements ConsumerInterface
{
    public function setContainer(ContainerInterface $container) {
        $this->container = $container;
        $this->jpush_key = $this->container->getParameter("jpush_key_hpl");
        $this->jpush_secret = $this->container->getParameter("jpush_secret_hpl");
        $this->jpush_debug = $this->container->getParameter("jpush_debug");
    }

    public function execute(AMQPMessage $msg){
        echo "{$msg->body}\n";
        $msg = unserialize($msg->body);
        //通过custom_tag 扩展jpush 推送
        $customTag = isset($msg['custom_tag']) ? $msg['custom_tag'] : false;
        if(!empty($customTag)){
            $this->handleExtend($msg);
        }
        return true;
    }

    private function handleExtend($msg){
        if($msg['custom_tag'] == 'hpl_jpush'){
            $userNames = $this->getUserNamesCustomtag($msg["user_names"]);
        }
        else{
            return;
        }
        $alert = $msg['alert'];
        $extras = $msg['extras'];
        $this->send($userNames, $alert, $extras);
    }

    private function getUserNamesCustomtag($usernames){
        if ($usernames === 'all') {
            return $this->jpush_debug ? M\all : null;
        }
        if (count($usernames) === 0) {
            return null;
        }
        return M\Audience(M\Alias($usernames));
    }

    private function send($audience, $alert, $extras){
        if (empty($audience)) {
            return 0;
        }
        try{
            $client = new JPushClient($this->jpush_key, $this->jpush_secret);
            $result = $client->push()
                ->setPlatform(M\all)
                ->setOptions(M\options(null, null, null, true, null))
                ->setAudience($audience)
                ->setNotification(M\notification(
                                    M\ios($alert, null, null, null, $extras, null),
                                    M\android($alert, null, null, $extras)))
                ->send();
            $ret = $result->json;
            echo "Done with return code: {$ret}\n";
        }
        catch(APIRequestException $e){
            $ret = $e->json;
            echo "exception with return code: {$ret}\n";
        }
        return $ret;
    }
}