<?php

namespace Voelkel\DataTablesBundle\Tests\Table;

use Voelkel\DataTablesBundle\Table\Column;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $column = new Column('test_name', 'testField');

        $this->assertEquals('test_name', $column->getName());
        $this->assertEquals('testField', $column->getField());
        $this->assertTrue(is_array($column->getOptions()));
    }

    public function testSearchableImpliedIfFilterIsSet()
    {
        $column = new Column('test_name', 'testField');
        $this->assertTrue($column->getOptions()['searchable']);

        $column = new Column('test_name', 'testField', [
            'searchable' => false,
        ]);
        $this->assertFalse($column->getOptions()['searchable']);

        $column = new Column('test_name', 'testField', [
            'filter' => 'text',
            'searchable' => false,
        ]);
        $this->assertTrue($column->getOptions()['searchable']);
    }

    public function testGetLabel()
    {
        $column = new Column('test_name', 'testField');
        $this->assertEquals('test_name', $column->getLabel());

        $column = new Column('test_name', 'testField', [
            'label' => 'Test label',
        ]);
        $this->assertEquals('Test label', $column->getLabel());
    }
}
