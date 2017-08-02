<?php

namespace Voelkel\DataTablesBundle\Tests\Table;

use Voelkel\DataTablesBundle\Table\AbstractTableDefinition;
use Voelkel\DataTablesBundle\Table\TableDefinition;
use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Column\EntityColumn;

class TestDefinition extends AbstractTableDefinition
{
    public function __construct($entity, $name, $prefix = null)
    {
        parent::__construct($entity, $name, $prefix);
    }

    protected function build() {}
}

class AbstractTableDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function testTableDefinitionConstructor()
    {
        $table = new TestDefinition('AppBundle\Entity\Test', 'test');
        $table->setContainer(null);

        $this->assertEquals('t', $table->getPrefix());
    }

    public function testGetters()
    {
        $table = new TestDefinition('AppBundle\Entity\Test', 'test');
        $table->setContainer(null);

        $this->assertEquals('AppBundle\Entity\Test', $table->getEntity());
        $this->assertEquals('test', $table->getName());
        $this->assertEquals('t', $table->getPrefix());
        $this->assertTrue(is_array($table->getColumns()));
    }

    public function testAddColumn()
    {
        $table = new TestDefinition('AppBundle\Entity\Test', 'test');

        $column = new Column('test_field', 'testField');
        $table->addColumn($column);

        $this->setExpectedException('\Exception', 'a column with the name "test_field" already exists.');
        $table->addColumn($column);
    }

    public function testGetColumn()
    {
        $table = new TestDefinition('AppBundle\Entity\Test', 'test');

        $column = new Column('test_field', 'testField');
        $table->addColumn($column);
        $this->assertEquals($column, $table->getColumn('test_field'));

        $this->setExpectedException('\Exception', 'unknown column "wrong"');
        $table->getColumn('wrong');
    }

    public function testGetSetConditionCallback()
    {
        $table = new TestDefinition('AppBundle\Entity\Test', 'test');

        $this->assertNull($table->getConditionCallback());

        $table->setConditionCallback(function() {});
        $this->assertInstanceOf('Closure', $table->getConditionCallback());
    }

    public function testGetSetResultCallback()
    {
        $table = new TestDefinition('AppBundle\Entity\Test', 'test');

        $this->assertNull($table->getResultCallback());

        $table->setResultCallback(function() {});
        $this->assertInstanceOf('Closure', $table->getResultCallback());
    }

    public function testGetHasColumnFilter()
    {
        $table = new TestDefinition('AppBundle\Entity\Test', 'test');

        $this->assertFalse($table->getHasColumnFilter());

        $table->addColumn(new Column('test_field', 'testField', [
            'filter' => 'text',
        ]));
        $this->assertTrue($table->getHasColumnFilter());
    }

    public function testGetJoinPrefixes()
    {
        $table = new TestDefinition('AppBundle\Entity\Test', 'test');

        $this->assertTrue(is_array($table->getJoinPrefixes()));
    }
}
