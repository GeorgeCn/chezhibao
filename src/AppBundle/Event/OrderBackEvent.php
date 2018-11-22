<?php

namespace AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class OrderBackEvent extends Event
{
    private $report;

    public function __construct($report){
        $this->report = $report;
    }

    public function getReport(){
        return $this->report;
    }
}