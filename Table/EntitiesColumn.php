<?php

namespace Voelkel\DataTablesBundle\Table;

class EntitiesColumn extends EntityColumn
{
    public function __construct($name, $field, $entityField, $entityPrefix, array $options = [])
    {
        $options['sortable'] = false;

        parent::__construct($name, $field, $entityField, $entityPrefix, $options);
    }
}
