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

    protected $options = [
        'stateSave' => false,
        'stateDuration' => 7200, // -1 sessionStorage. 0 or greater localStorage. 0 infinite. > 0 duration in seconds
    ];

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

    /**
     * @param string $entity
     * @param string $name
     * @param string|null $serviceId
     */
    public function __construct($entity = null, $name = null, $serviceId = null)
    {
        $this->entity = $entity;
        $this->name = $name;
        $this->prefix = $this->name[0];
        $this->serviceId = $serviceId;

        $settings = [
            'entity'  => null,
            'name'    => null,
            'service' => null,
        ];

        $this->getSettings($settings);

        if (null !== $settings['entity']) {
            $this->entity = $settings['entity'];
        }

        if (null !== $settings['name']) {
            $this->name = $settings['name'];
            $this->prefix = $this->name[0];
        }

        if (null !== $settings['service']) {
            $this->serviceId = $settings['service'];
        }

        $options = $this->configureOptions();
        $this->options = array_merge($this->options, $options);

        $this->build();
    }

    protected function getSettings(array &$settings) { }

    protected function configureOptions() { return []; }

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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
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
                $result[] = $column->getEntityPrefix();
            }
        }

        return array_unique($result);
    }
}
