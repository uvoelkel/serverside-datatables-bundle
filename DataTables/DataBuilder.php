<?php

namespace Voelkel\DataTablesBundle\DataTables;

use Voelkel\DataTablesBundle\Table\AbstractDataTable;
use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Column\EntitiesScalarColumn;
use Voelkel\DataTablesBundle\Table\Column\EntityColumn;
use Voelkel\DataTablesBundle\Table\Column\EntitiesColumn;
use Voelkel\DataTablesBundle\Table\Column\EntitiesCountColumn;

class DataBuilder
{
    /**
     * @param AbstractDataTable $table
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param Response $response
     * @param DataToStringConverter $dataToStringConverter
     * @throws \Exception
     */
    static public function build(
        AbstractDataTable $table,
        \Doctrine\ORM\QueryBuilder $qb,
        Response $response,
        DataToStringConverter $dataToStringConverter,
        $rowCallback = null
    ) {
        $entities = $qb->getQuery()->getResult();

        foreach ($entities as $result) {
            $entity = $result;
            if ($table->getHasScalarColumns()) {
                $entity = $result[0];
            }

            $tmp = [];

            if (method_exists($entity, 'getId')) {
                $tmp['DT_RowId'] = 'row_' . $entity->getId();
                $tmp['DT_RowAttr'] = ['data-entity' => $entity->getId()];
                // DT_RowClass
                // DT_RowData
            }

            if (is_callable($rowCallback)) {
                $cbResult = $rowCallback($entity, $table);
                $tmp = array_merge($tmp, $cbResult);
            }

            foreach ($table->getColumns() as $column) {
                if ($column instanceof EntitiesCountColumn) {
                    $tmp[$column->getName()] = $result[$column->getField() . '_count'];
                } elseif ($column instanceof EntitiesScalarColumn) {
                    $tmp[$column->getName()] = $result[$column->getField() . '_' . $column->getOperation()];
                } else {
                    $tmp[$column->getName()] = self::getColumnProperty($entity, $column, $dataToStringConverter);
                }
            }

            $response->data[] = $tmp;
        }
    }

    /**
     * @param mixed $object
     * @param Column $column
     * @param DataToStringConverter $dataToStringConverter
     * @return string
     * @throws \Exception
     */
    static private function getColumnProperty($object, Column $column, DataToStringConverter $dataToStringConverter)
    {
        $data = null;

        if ($column instanceof EntityColumn) {
            $object = self::callGetterByColumName($object, $column->getField(), $column);
            if (null !== $object) {
                $data = self::callGetterByColumName($object, $column->getEntityField(), $column);
            }
        } else {
            if (true === $column->getOptions()['unbound']) {
                $data = $object;
            } else {
                $data = self::callGetterByColumName($object, $column->getField(), $column);
            }
        }

        if (isset($column->getOptions()['format_data_callback'])) {
            $callback = $column->getOptions()['format_data_callback'];

            if ($callback instanceof \Closure || is_array($callback)) {
                return call_user_func($callback, $data, $object, $column);
            }

            throw new \Exception(sprintf('invalid "format_data_callback" of type "%s"', get_class($callback)));
        }

        return $dataToStringConverter->convertDataToString($data);
    }

    /**
     * @param mixed $object
     * @param string $name
     * @param Column $column
     * @return mixed
     * @throws \Exception
     */
    static private function callGetterByColumName($object, $name, Column $column)
    {
        $methods = [];
        $methods[] = 'get' . ucfirst($name);

        foreach ($methods as $method) {

            if (is_array($object) || $object instanceof \ArrayAccess) {
                if (!($column) instanceof EntitiesColumn) {
                    throw new \Exception(sprintf('unexpected array data for column "%s"', $column->getName()));
                }

                if (sizeof($object) > $column->getOptions()['display_join_max_entries']) {
                    return '... ' . sizeof($object);
                }

                $result = [];
                foreach ($object as $entity) {
                    if (method_exists($entity, $method)) {
                        $result[] = $entity->$method();
                    }
                }
                return join($column->getOptions()['display_join_glue'], $result);

            } else {

                $sub = null;
                $pos = strpos($method, '.');
                if (false !== $pos) {
                    $sub = substr($method, $pos + 1);
                    $method = substr($method, 0, $pos);
                }

                if (method_exists($object, $method)) {
                    $result = $object->$method();

                    if (null !== $sub && null !== $result) {
                        $result = self::callGetterByColumName($result, $sub, $column);
                    }

                    return $result;
                }
            }
        }

        throw new \Exception(sprintf('no getter found for property "%s" in object of class "%s".', $name, get_class($object)));
    }
}
