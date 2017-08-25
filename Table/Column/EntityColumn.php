<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class EntityColumn extends Column
{
    /** @var string */
    private $entityField;

    /** @var string[] */
    private $fields = [];

    /** @var string[] */
    private $prefixes = [];

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

        $pos = strpos($field, '.');
        if (false !== $pos) {
            $fields = $field;

            while (false !== $pos) {
                $sub = substr($fields, 0, $pos);
                $this->fields[] = $sub;
                $this->prefixes[$sub] = EntityColumn::createEntityPrefix($sub);

                $fields = substr($fields, $pos + 1);
                $pos = strpos($fields, '.');

                if (false === $pos && 0 < strlen($fields)) {
                    $pos = strlen($fields);
                }
            }

        } else {
            $this->fields[] = $field;
            $this->prefixes[$field] = self::createEntityPrefix($field);
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
    public function getEntityPrefix($part = null)
    {
        return join('_', $this->getPrefixesInOrder($part));
    }

    /**
     * @return string[]
     */
    public function getEntityPrefixes()
    {
        $result = [];

        $prefix = '';
        foreach ($this->getPrefixesInOrder() as $pf) {
            $prefix .= (empty($prefix) ? '' : '_') . $pf;
            $result[] = $prefix;
        }

        return $result;
    }

    /**
     * @param null|string $part
     * @return string[]
     */
    private function getPrefixesInOrder(string $part = null)
    {
        $result = [];
        foreach ($this->fields as $field) {
            $result[] = $this->prefixes[$field];
            if (null !== $part && $part === $field) {
                break;
            }
        }
        return $result;
    }

    /**
     * @param string $field
     * @return string
     */
    static public function createEntityPrefix($field)
    {
        $fullField = $field;
        $prefix = $field[0];

        if (false !== ($pos = strpos($field, '_'))) {
            // snake_case
            do {
                $field = substr($field, $pos + 1);
                $prefix .= $field[0];
                $pos = strpos($field, '_');
            } while (false !== $pos);
        } else {
            // camelCase
            $camel = strpbrk($field, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            while (0 < strlen($camel) && strlen($camel) < strlen($field)) {
                $prefix .= strtolower($camel[0]);

                $field = $camel;
                $camel = substr($camel, 1);
                $camel = strpbrk($camel, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            }
        }


        $number = 0;
        do {
            $result = $prefix . '_' . $number;

            if (!isset(self::$tableWidePrefixes[$result])) {
                self::$tableWidePrefixes[$result] = $fullField;
                break;
            } elseif (self::$tableWidePrefixes[$result] === $fullField) {
                break;
            } elseif (self::$tableWidePrefixes[$result] !== $fullField) {
                $number++;
            }
        } while (true);

        return $result;
    }
}
