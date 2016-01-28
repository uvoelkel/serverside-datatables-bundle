<?php

namespace Voelkel\DataTablesBundle\Table;

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

    /** @var null|callable */
    protected $conditionCallback;

    /** @var null|callable */
    protected $resultCallback;

    protected $hasCountColumns = false;

    protected $hasColumnFilter = false;

    /**
     * @param string $entity
     * @param string $name
     * @param string|null $prefix
     */
    protected function __construct($entity, $name, $prefix = null)
    {
        $this->entity = $entity;
        $this->name = $name;
        $this->prefix = $prefix;

        if (null === $this->prefix) {
            $this->prefix = substr($this->name, 0, 1);
        }

        $this->build();
    }

    protected function build() { }

    public function getEntity()
    {
        return $this->entity;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function addColumn(Column $column)
    {
        if ($column instanceof EntityColumn && $column->getEntityPrefix() === $this->prefix) {
            throw new \Exception('the entity prefix is already used.');
        }

        if (isset($this->columns[$column->getName()])) {
            throw new \Exception('a column with the same name already exists.');
        }

        $this->columns[$column->getName()] = $column;

        if ($column instanceof EntityCountColumn) {
            $this->hasCountColumns = true;
        }

        if (false !== $column->getOptions()['filter']) {
            $this->hasColumnFilter = true;
        }

        return $this;
    }

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

    public function getConditionCallback()
    {
        return $this->conditionCallback;
    }

    public function setResultCallback(callable $callback)
    {
        $this->resultCallback = $callback;
    }

    public function getResultCallback()
    {
        return $this->resultCallback;
    }

    public function getHasCountColumns()
    {
        return $this->hasCountColumns;
    }

    public function getHasColumnFilter()
    {
        return $this->hasColumnFilter;
    }

    public function getJoinPrefixes()
    {
        $result = [];

        foreach ($this->columns as $column) {
            if ($column instanceof EntityColumn) {
                $result[] = $column->getEntityPrefix();
            }
        }

        return array_unique($result);
    }
}
