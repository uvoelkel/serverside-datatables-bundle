<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class EntityColumn extends Column
{
    /** @var string */
    private $entityField;

    /** @var string[] */
    private $prefixes = [];

    /**
     * @param string $name
     * @param string $field
     * @param string $entityField
     * @param array $options
     */
    public function __construct($name, $field, $entityField, array $options = [])
    {
        $this->entityField = $entityField;

        $pos = strpos($field, '.');
        if (false !== $pos) {
            $fields = $field;

            while (false !== $pos) {
                $sub = substr($fields, 0, $pos);
                $this->prefixes[] = EntityColumn::createEntityPrefix($sub);

                $fields = substr($fields, $pos + 1);
                $pos = strpos($fields, '.');

                if (false === $pos && 0 < strlen($fields)) {
                    $pos = strlen($fields);
                }
            }

        } else {
            $this->prefixes[] = self::createEntityPrefix($field);
        }

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
        return join('_', $this->prefixes);
    }

    /**
     * @return string[]
     */
    public function getEntityPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * @param string $field
     * @return string
     */
    static public function createEntityPrefix($field)
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
