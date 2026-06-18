<?php

namespace Voelkel\DataTablesBundle\Table;

use Voelkel\DataTablesBundle\Table\Column\ActionsColumn;
use Voelkel\DataTablesBundle\Table\Column\CallbackColumn;
use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Column\EmbeddedEntityColumn;
use Voelkel\DataTablesBundle\Table\Column\EntityColumn;
use Voelkel\DataTablesBundle\Table\Column\UnboundColumn;

class TableBuilder implements TableBuilderInterface
{
    /** @var Column[] */
    private array $columns = [];

    /** @var null|callable */
    private $conditionCallback;

    /** @var callable[] */
    private $conditionCallbacks = [];

    /** @var null|callable */
    private $orderCallback;

    /** @var null|callable */
    private $resultCallback;

    /** @var null|callable */
    private $rowCallback;

    private AbstractDataTable $table;

    public function __construct(AbstractDataTable $table)
    {
        $this->table = $table;
    }

    public function add(string $field, ?string $class = null, array $options = []): TableBuilderInterface
    {
        $fields = explode('.', $field);

        if (null === $class) {
            $class = Column::class;
        }

        if (sizeof($fields) > 1 && Column::class === $class) {
            $class = EntityColumn::class;

            if (null !== ($metadata = $this->table->getMetadata()) && isset($metadata->embeddedClasses[$fields[0]])) {
                $class = EmbeddedEntityColumn::class;
            }
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
            case EmbeddedEntityColumn::class:
                $entityField = array_pop($fields);
                $field = join('.', $fields);
                $this->columns[] = new EmbeddedEntityColumn($name, $field, $entityField, $options);
                break;
            case ActionsColumn::class:
                $actions = $options['actions'];
                unset($options['actions']);
                $this->columns[] = new ActionsColumn($name, $actions, $options);
                break;
            case CallbackColumn::class:
                $callback = $options['callback'];
                unset($options['callback']);
                $this->columns[] = new CallbackColumn($name, $field, $callback, $options);
                break;
            case UnboundColumn::class:
                $callback = $options['callback'];
                unset($options['callback']);
                $this->columns[] = new UnboundColumn($name, $callback, $options);
                break;
            default:
                throw new \Exception('unhandled column class ' . $class);
                break;
        }

        return $this;
    }

    public function addColumn(Column $column): TableBuilderInterface
    {
        $this->columns[] = $column;
        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setConditionCallback(callable $callback): TableBuilderInterface
    {
        @trigger_error('setConditionCallback() is deprecated use addConditionCallback() instead', E_USER_DEPRECATED);
        $this->conditionCallback = $callback;
        return $this;
    }

    public function getConditionCallback()
    {
        return $this->conditionCallback;
    }

    public function addConditionCallback(callable $callback): TableBuilderInterface
    {
        $this->conditionCallbacks[] = $callback;
        return $this;
    }

    public function getConditionCallbacks(): array
    {
        return $this->conditionCallbacks;
    }

    public function setOrderCallback(callable $callback): TableBuilderInterface
    {
        $this->orderCallback = $callback;
        return $this;
    }

    public function getOrderCallback()
    {
        return $this->orderCallback;
    }

    public function setResultCallback(callable $callback): TableBuilderInterface
    {
        $this->resultCallback = $callback;
        return $this;
    }

    public function getResultCallback()
    {
        return $this->resultCallback;
    }

    public function setRowCallback(callable $callback): TableBuilderInterface
    {
        $this->rowCallback = $callback;
        return $this;
    }

    public function getRowCallback()
    {
        return $this->rowCallback;
    }
}
