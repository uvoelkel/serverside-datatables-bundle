<?php

namespace Voelkel\DataTablesBundle\Tests\Table;

use Voelkel\DataTablesBundle\Table\EntityColumn;

class EntityColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $column = new EntityColumn('test_name', 'testField', 'testAssociation', 'ta');

        $this->assertEquals('testAssociation', $column->getEntityField());
        $this->assertEquals('ta', $column->getEntityPrefix());
    }
}
