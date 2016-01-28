<?php

namespace Voelkel\DataTablesBundle\Table;

// todo rename to EntitiesCountColumn !?
class EntityCountColumn extends EntityColumn
{
    /**
     * @param string $name
     * @param string $field
     * @param string $entityPrefix
     * @param array $options
     */
    public function __construct($name, $field, $entityPrefix, array $options = [])
    {
        parent::__construct($name, $field, '', $entityPrefix, $options);
    }
}
