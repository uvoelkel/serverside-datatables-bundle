<?php

namespace Voelkel\DataTablesBundle\Table;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Column\EntityColumn;
use Voelkel\DataTablesBundle\Table\Column\EntitiesCountColumn;

abstract class AbstractDataTable implements ContainerAwareInterface
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
     * function(\Voelkel\DataTablesBundle\Table\AbstractDataTable $table, \Doctrine\ORM\QueryBuilder $qb, \Voelkel\DataTablesBundle\DataTables\Response $response)
     */
    protected $resultCallback;

    /** @var bool */
    protected $hasCountColumns = false;

    /** @var bool */
    protected $hasColumnFilter = false;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface|null */
    protected $container = null;

    /**
     * @inheritdoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        if (null !== $this->container) {
            return;
        }

        $this->container = $container;

        $settings = new TableSettings();
        $options = (null !== $this->container) ?
            $this->container->get('serverside_datatables.table_options_factory')->create() :
            new TableOptions();

        $this->configure($settings, $options);

        if (null !== $settings->getEntity()) {
            $this->entity = $settings->getEntity();
        }

        if (null !== $settings->getName()) {
            $this->name = $settings->getName();
        }

        if (null !== $settings->getServiceId()) {
            $this->serviceId = $settings->getServiceId();
        }

        $this->prefix = $this->name[0];
        $this->options = array_merge($this->options, $options->all());

        $this->build();
    }

    /**
     * @return null|ContainerInterface
     * @internal
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function get($serviceId)
    {
        if (null === $this->container) {
            return null;
        }

        return $this->container->get($serviceId);
    }

    //protected function getSettings(array &$settings) { }

    //protected function configureOptions() { return []; }

    protected function configure(TableSettings $settings, TableOptions $options)
    {
        if (method_exists($this, 'getSettings')) {
            @trigger_error(
                'The use of "getSettings()" is deprecated. Use "configure()" instead.',
                E_USER_DEPRECATED
            );

            $arraySettings = [
                'entity' => null,
                'name' => null,
                'service' => null,
            ];

            $this->getSettings($arraySettings);

            if (null !== $arraySettings['entity']) {
                $settings->setEntity($arraySettings['entity']);
            }

            if (null !== $arraySettings['name']) {
                $settings->setName($arraySettings['name']);
            }

            if (null !== $arraySettings['service']) {
                $settings->setServiceId($arraySettings['service']);
            }
        }

        if (method_exists($this, 'configureOptions')) {
            @trigger_error(
                'The use of "configureOptions()" is deprecated. Use "configure()" instead.',
                E_USER_DEPRECATED
            );

            $arrayOptions = $this->configureOptions();
            foreach ($arrayOptions as $key => $value) {
                $options[$key] = $value;
            }
        }
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
