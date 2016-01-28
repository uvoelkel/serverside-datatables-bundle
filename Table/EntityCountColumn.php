<?php

namespace Voelkel\DataTablesBundle\Table;

// todo rename to EntitiesCountColumn !?
class EntityCountColumn extends EntityColumn
{
    public function __construct($name, $field, $entityPrefix, array $options = [])
    {
        parent::__construct($name, $field, '', $entityPrefix, $options);
    }
}
