<?php

namespace Voelkel\DataTablesBundle\Table\Column;

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
     * @param array $options
     */
    public function __construct($name, $field, $entityField, array $options = [])
    {
        $this->entityField = $entityField;
        $this->entityPrefix = $this->createEntityPrefix($field);

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

    /**
     * @param string $field
     * @return string
     */
    private function createEntityPrefix($field)
    {
        $result = $field[0];

        if (false !== ($pos = strpos($field, '_'))) {
            // snake_case
            do {
                $field = substr($field, $pos + 1);
                $result .= $field[0];
                $pos = strpos($field, '_');
            } while (false !== $pos);
        } else {
            // camelCase
            $camel = strpbrk($field, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            while (0 < strlen($camel) && strlen($camel) < strlen($field)) {
                $result .= strtolower($camel[0]);

                $field = $camel;
                $camel = substr($camel, 1);
                $camel = strpbrk($camel, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            }
        }

        return $result;
    }
}
