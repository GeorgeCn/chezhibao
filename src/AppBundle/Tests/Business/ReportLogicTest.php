<?php

namespace AppBundle\Tests\Business;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DefaultControllerTest extends KernelTestCase
{
    private function getLogic()
    {
        return static::$kernel->getContainer()->get("ReportLogic");
    }

    private function getDoctrine()
    {
        return static::$kernel->getContainer()->get("doctrine");
    }

    protected function setUp()
    {
        static::bootKernel();
    }

    public function testGetDiffReport()
    {
        $logic = $this->getLogic();

        $report = $this->getDoctrine()->getRepository("AppBundle:Report")->find(10);
        $logic->getDiffReport($report);
    }
}
