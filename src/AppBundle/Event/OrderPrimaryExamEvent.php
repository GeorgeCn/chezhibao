<?php

namespace AppBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class OrderPrimaryExamEvent extends Event
{
    protected $report;

    public function __construct($report)
    {
        $this->report = $report;
    }

    public function getReport(){
        return $this->report;
    }
}