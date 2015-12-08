<?php

namespace Voelkel\DataTablesBundle\Tests\DataTables;

class RequestTest extends \PHPUnit_Framework_TestCase
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

    public function testGetGlobalSearchValue()
    {
        $sfRequest = new \Symfony\Component\HttpFoundation\Request([
            'draw' => 42,
            'start' => 25,
            'length' => 50,
            'search' => [
                'regex' => 'false',
                'value' => 'searchstring',
            ],
        ]);

        $dtRequest = new \Voelkel\DataTablesBundle\DataTables\Request($sfRequest);

        $this->assertEquals('searchstring', $dtRequest->getSearchValue());
    }

    public function testGetColumnSearchValue()
    {
        $sfRequest = new \Symfony\Component\HttpFoundation\Request([
            'draw' => 42,
            'start' => 25,
            'length' => 50,
            'columns' => [
                0 => [
                    'name' => 'column_1',
                    'search' => [
                        'regex' => 'false',
                        'value' => 'searchstring',
                    ],
                ],
            ],
        ]);

        $dtRequest = new \Voelkel\DataTablesBundle\DataTables\Request($sfRequest);

        $this->assertEquals('searchstring', $dtRequest->getSearchValue(0));
        $this->assertEquals('searchstring', $dtRequest->getSearchValue('column_1'));
        $this->assertNull($dtRequest->getSearchValue('column_99'));
    }
}
