<?php

namespace AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class OrderSubmitEvent extends Event
{
    private $order;

    public function __construct($order){
        $this->order = $order;
    }

    public function getOrder(){
        return $this->order;
    }
}