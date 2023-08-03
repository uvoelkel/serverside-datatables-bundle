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

    public function setConditionCallback(callable $callback): void
    {
        $this->conditionCallback = $callback;
    }

    public function getConditionCallback(): ?callable
    {
        return $this->conditionCallback;
    }
}
