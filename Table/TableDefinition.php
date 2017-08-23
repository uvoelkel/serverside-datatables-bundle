<?php

namespace Voelkel\DataTablesBundle\Table;

class TableDefinition extends AbstractDataTable
{
    /**
     * @param string $entity
     * @param string $name
     */
    public function __construct($entity, $name)
    {
        $this->entity = $entity;
        $this->name = $name;
    }

    protected function build() { }
}
