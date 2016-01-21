<?php

namespace Voelkel\DataTablesBundle\DataTables;

use Voelkel\DataTablesBundle\Table\AbstractTableDefinition;
use Voelkel\DataTablesBundle\Table\Column;
use Voelkel\DataTablesBundle\Table\EntityColumn;
use Voelkel\DataTablesBundle\Table\EntityCountColumn;

class DataBuilder
{
    /**
     * @param AbstractTableDefinition $table
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param Response $response
     */
    static public function build(AbstractTableDefinition $table, \Doctrine\ORM\QueryBuilder $qb, Response $response) {
        $entities = $qb->getQuery()->getResult();

        foreach ($entities as $result) {
            $entity = $result;
            if ($table->getHasCountColumns()) {
                $entity = $result[0];
            }

            $tmp = [];

            if (method_exists($entity, 'getId')) {
                $tmp['DT_RowId'] = 'row_' . $entity->getId();
                $tmp['DT_RowAttr'] = ['data-entity' => $entity->getId()];
                // DT_RowClass
                // DT_RowData
            }

            foreach ($table->getColumns() as $column) {
                if (!($column instanceof EntityCountColumn)) {
                    $tmp[$column->getName()] = self::getColumnProperty($entity, $column);
                } else {
                    $tmp[$column->getName()] = $result[$column->getField() . '_count'];
                }
            }

            $response->data[] = $tmp;
        }
    }

    /**
     * @param mixed $object
     * @param Column $column
     * @return string
     * @throws \Exception
     */
    static public function getColumnProperty($object, Column $column)
    {
        $data = null;

        if ($column instanceof EntityColumn) {
            $object = self::callGetterByColumName($object, $column->getField());
            if (null !== $object) {
                $data = self::callGetterByColumName($object, $column->getEntityField());
            }
        } else {
            if (true === $column->getOptions()['unbound']) {
                $data = $object;
            } else {
                $data = self::callGetterByColumName($object, $column->getField());
            }
        }

        if (isset($column->getOptions()['format_data_callback'])) {
            $callback = $column->getOptions()['format_data_callback'];

            if ($callback instanceof \Closure) {
                return call_user_func($callback, $data, $column, $object);
                //return $callback($data, $column);
            }

            throw new \Exception(sprintf('invalid "format_data_callback" of type "%s"', get_class($callback)));
        }

        return self::convertDataToString($data);
    }

    /**
     * @param mixed $object
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    static private function callGetterByColumName($object, $name)
    {
        $methods = [];
        $methods[] = 'get' . ucfirst($name);

        foreach ($methods as $method) {
            if (method_exists($object, $method)) {
                return $object->$method();
            }
        }

        throw new \Exception(sprintf('no getter found for property "%s" in object of class "%s".', $name, get_class($object)));
    }

    /**
     * @param mixed $data
     * @return string
     */
    static private function convertDataToString($data)
    {
        if (is_object($data)) {
            if ($data instanceof \DateTime) {
                return $data->format('d.m.Y H:i:s');
            }

            if (method_exists($data, '__toString')) {
                return $data->__toString();
            }

            return get_class($data);
        } elseif (is_bool($data)) {
            return $data ? 'true' : 'false';
        }

        return $data;
    }
}
