<?php

namespace Voelkel\DataTablesBundle\Table;

class TableDefinition extends AbstractTableDefinition
{
    /**
     * @param string $entity
     * @param string $name
     * @param string|null $prefix
     */
    public function __construct($entity, $name, $prefix = null)
    {
        parent::__construct($entity, $name, $prefix);
    }
}
