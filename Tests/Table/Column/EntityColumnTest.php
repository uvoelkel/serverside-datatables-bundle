<?php

namespace Voelkel\DataTablesBundle\Tests\Table\Column;

use Voelkel\DataTablesBundle\Table\Column\EntityColumn;

class EntityColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $column = new EntityColumn('test_name', 'testField', 'testAssociation', 'ta');

        $this->assertEquals('testAssociation', $column->getEntityField());
        $this->assertEquals('ta', $column->getEntityPrefix());
    }
}
