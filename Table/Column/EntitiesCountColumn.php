<?php

namespace Voelkel\DataTablesBundle\Table\Column;

/**
 * If the table entity is the inverse side (OneToMany) of an association
 * the EntitiesCountColumn can be used to count the associated entities
 */
class EntitiesCountColumn extends EntityColumn
{
    /**
     * @param string $name
     * @param string $field
     * @param array $options
     */
    public function __construct($name, $field, array $options = [])
    {
        parent::__construct($name, $field, '', $options);
    }
}
