<?php

namespace Voelkel\DataTablesBundle\Table;

class EntityColumn extends Column
{
    /** @var string */
    private $entityField;

    /** @var string */
    private $entityPrefix;

    /**
     * @param string $name
     * @param string $field
     * @param string $entityField
     * @param string $entityPrefix
     * @param array $options
     */
    public function __construct($name, $field, $entityField, $entityPrefix, array $options = [])
    {
        $this->entityField = $entityField;
        $this->entityPrefix = $entityPrefix;

        parent::__construct($name, $field, $options);
    }

    /**
     * @return string
     */
    public function getEntityField()
    {
        return $this->entityField;
    }

    /**
     * @return string|null
     */
    public function getEntityPrefix()
    {
        return $this->entityPrefix;
    }
}
