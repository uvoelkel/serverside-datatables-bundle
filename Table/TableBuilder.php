<?php

namespace Voelkel\DataTablesBundle\Table;

use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Column\EntityColumn;

class TableBuilder implements TableBuilderInterface
{
    private $columns = [];

    public function add(string $field, $class = null, $options = [])
    {
        $fields = explode('.', $field);

        if (null === $class) {
            $class = Column::class;
        }

        if (sizeof($fields) > 1 && Column::class === $class) {
            $class = EntityColumn::class;
        }

        if (isset($options['name'])) {
            $name = $options['name'];
        } else {
            $name = join('_', $fields);
        }

        switch ($class) {
            case Column::class:
                $this->columns[] = new Column($name, $field, $options);
                break;
            case EntityColumn::class:
                $entityField = array_pop($fields);
                $field = join('.', $fields);
                $this->columns[] = new EntityColumn($name, $field, $entityField, $options);
                break;
            default:
                throw new \Exception('unhandled column class ' . $class);
                break;
        }

        return $this;
    }

    public function addColumn(Column $column)
    {
        /*if (isset($this->columns[$column->getName()])) {
            throw new \Exception(sprintf('a column with the name "%s" already exists.', $column->getName()));
        }*/

        $this->columns[] = $column;

        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }
}
