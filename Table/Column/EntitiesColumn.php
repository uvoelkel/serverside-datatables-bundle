<?php

namespace Voelkel\DataTablesBundle\Table\Column;

/**
 * If the table entity is the inverse side (OneToMany) of an association
 * the EntitiesColumn can be used to get the associated entities and join their field values
 */
class EntitiesColumn extends EntityColumn
{
    public function __construct($name, $field, $entityField, array $options = [])
    {
        if (isset($options['filter_empty']) && true === $options['filter_empty']) {
            throw new \Exception('filtering for empty values is not allowed for EntitiesColumn');
        }

        if (isset($options['sortable']) && true === $options['sortable']) {
            throw new \Exception('sortable = true is not allowed for EntitiesColumn');
        }

        // default
        $options['sortable'] = false;
        // column type specific
        if (!isset($options['display_join_max_entries'])) {
            $options['display_join_max_entries'] = 5;
        }
        if (!isset($options['display_join_glue'])) {
            $options['display_join_glue'] = ', ';
        }

        parent::__construct($name, $field, $entityField, $options);
    }
}
