<?php

namespace Voelkel\DataTablesBundle\Table;

use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Column\EntityColumn;
use Voelkel\DataTablesBundle\Table\Column\EntitiesCountColumn;

abstract class AbstractTableDefinition
{
    /** @var Column[] */
    protected $columns = [];

    /** @var string */
    protected $entity;

    /** @var string */
    protected $name;

    /** @var string */
    protected $prefix;

    /** @var null|string */
    protected $serviceId;

    /** @var null|callable */
    protected $conditionCallback;

    /**
     * @var null|callable
     *
     * function(\Voelkel\DataTablesBundle\Table\AbstractTableDefinition $table, \Doctrine\ORM\QueryBuilder $qb, \Voelkel\DataTablesBundle\DataTables\Response $response)
     */
    protected $resultCallback;

    /** @var bool */
    protected $hasCountColumns = false;

    /** @var bool */
    protected $hasColumnFilter = false;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface|null */
    public $container = null;

    /**
     * @param string $entity
     * @param string $name
     * @param string|null $serviceId
     */
    protected function __construct($entity, $name, $serviceId = null)
    {
        $this->entity = $entity;
        $this->name = $name;
        $this->prefix = $this->name[0];
        $this->serviceId = $serviceId;

        $this->build();
    }

    protected function build() { }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return null|string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param Column $column
     * @return $this
     * @throws \Exception
     */
    public function addColumn(Column $column)
    {
        if (isset($this->columns[$column->getName()])) {
            throw new \Exception(sprintf('a column with the name "%s" already exists.', $column->getName()));
        }

        $this->columns[$column->getName()] = $column;

        if ($column instanceof EntitiesCountColumn) {
            $this->hasCountColumns = true;
        }

        if (false !== $column->getOptions()['filter']) {
            $this->hasColumnFilter = true;
        }

        return $this;
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param $name
     * @return Column
     * @throws \Exception
     */
    public function getColumn($name)
    {
        if (isset($this->columns[$name])) {
            return $this->columns[$name];
        }

        throw new \Exception(sprintf('unknown column "%s"', $name));
    }

    /**
     * @param callable $callback
     *
     * function(\Doctrine\ORM\QueryBuilder $qb) {}
     */
    public function setConditionCallback(callable $callback)
    {
        $this->conditionCallback = $callback;
    }

    /**
     * @return callable|null
     */
    public function getConditionCallback()
    {
        return $this->conditionCallback;
    }

    /**
     * @param callable $callback
     */
    public function setResultCallback(callable $callback)
    {
        $this->resultCallback = $callback;
    }

    /**
     * @return callable|null
     */
    public function getResultCallback()
    {
        return $this->resultCallback;
    }

    /**
     * @return bool
     */
    public function getHasCountColumns()
    {
        return $this->hasCountColumns;
    }

    /**
     * @return bool
     */
    public function getHasColumnFilter()
    {
        return $this->hasColumnFilter;
    }

    /**
     * @return string[]
     */
    public function getJoinPrefixes()
    {
        $result = [];

        foreach ($this->columns as $column) {
            if ($column instanceof EntityColumn) {
                $joined = '';
                foreach ($column->getEntityPrefixes() as $prefix) {
                    $joined .= (empty($joined) ? '' : '_') . $prefix;
                    $result[] = $joined;
                }
            }
        }

        return array_unique($result);
    }
}
