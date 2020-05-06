<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class EmbeddedEntityColumn extends Column
{
    /** @var string */
    private $entityField;

    private static $tableWidePrefixes = [];

    /**
     * @param string $name
     * @param string $field
     * @param string $entityField
     * @param array $options
     */
    public function __construct($name, $field, $entityField, array $options = [])
    {
        $this->entityField = $entityField;

        parent::__construct($name, $field, $options);
    }

    /**
     * @return string
     */
    public function getEntityField()
    {
        return $this->entityField;
    }
}
