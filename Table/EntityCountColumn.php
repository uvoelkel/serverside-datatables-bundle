<?php

namespace Voelkel\DataTablesBundle\Table;

class EntityCountColumn extends EntityColumn
{
    public function __construct($name, $field, $entityPrefix, array $options = [])
    {
        parent::__construct($name, $field, '', $entityPrefix, $options);
    }
}
