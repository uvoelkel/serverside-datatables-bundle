<?php

namespace Voelkel\DataTablesBundle\Tests\Controller;

use Voelkel\DataTablesBundle\Table\AbstractTableDefinition;

class TestTableDefinition extends AbstractTableDefinition
{
    public function __construct()
    {
        parent::__construct('Voelkel\DataTablesBundle\Tests\Controller\TestTableEntity', 'test');
    }
}
