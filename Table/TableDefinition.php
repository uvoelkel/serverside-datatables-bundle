<?php

namespace Voelkel\DataTablesBundle\Table;

class TableDefinition extends AbstractTableDefinition
{
    /**
     * @param string $entity
     * @param string $name
     */
    public function __construct($entity, $name)
    {
        parent::__construct($entity, $name);
    }
}
