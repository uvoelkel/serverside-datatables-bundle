<?php

namespace Voelkel\DataTablesBundle\Tests\DataTables;

use Voelkel\DataTablesBundle\Table\AbstractTableDefinition;
use Voelkel\DataTablesBundle\Table\CallbackColumn;
use Voelkel\DataTablesBundle\Table\Column;
use Voelkel\DataTablesBundle\Table\EntitiesColumn;
use Voelkel\DataTablesBundle\Table\EntityCountColumn;
use Voelkel\DataTablesBundle\Table\EntityColumn;
use Voelkel\DataTablesBundle\Table\UnboundColumn;

class TestTable extends AbstractTableDefinition
{
    public function __construct()
    {
        parent::__construct('Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestUser', 'user');
    }

    protected function build()
    {
        $this
            ->addColumn(new Column('id', 'id'))
            ->addColumn(new Column('name', 'name'))
            ->addColumn(new CallbackColumn('status', 'status', function($data) {
                if (123 === $data) {
                    return 'ABC';
                }
                return $data;
            }))
            ->addColumn(new EntitiesColumn('groups', 'groups', 'id', 'g'))
            ->addColumn(new UnboundColumn('name_unbound', function(\Voelkel\DataTablesBundle\Tests\DataTables\Entity\TestUser $data) {
                return '*' . $data->getName() . '*';
            }))
        ;
    }
}
