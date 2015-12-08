<?php

namespace Voelkel\DataTablesBundle\Tests\DataTables;

class DataBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $sfRequest = new \Symfony\Component\HttpFoundation\Request([
            'draw' => 42,
            'start' => 25,
            'length' => 50,
        ]);

        $dtRequest = new \Voelkel\DataTablesBundle\DataTables\Request($sfRequest);

        $this->assertEquals(42, $dtRequest->getDraw());
        $this->assertEquals(25, $dtRequest->getStart());
        $this->assertEquals(50, $dtRequest->getLength());
        $this->assertEquals(array(), $dtRequest->getColumns());
        $this->assertEquals(array(), $dtRequest->getOrder());
    }
}
