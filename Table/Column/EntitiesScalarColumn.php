<?php

namespace Voelkel\DataTablesBundle\Table\Column;

/**
 * If the table entity is the inverse side (OneToMany) of an association
 * the EntitiesScalarColumn can be used to ...
 */
class EntitiesScalarColumn extends EntityColumn
{
    const OPERATION_COUNT = 'count';
    const OPERATION_SUM = 'sum';

    /** @var string */
    private $operation;

    /**
     * @param string $name
     * @param string $field
     * @param string $operation
     * @param array $options
     */
    public function __construct($name, $field, $entityField, $operation, array $options = [])
    {
        $this->operation = $operation;

        parent::__construct($name, $field, $entityField, $options);
    }

    /**
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }
}
