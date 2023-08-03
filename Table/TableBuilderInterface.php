<?php

namespace Voelkel\DataTablesBundle\Table;

use Voelkel\DataTablesBundle\Table\Column\Column;

interface TableBuilderInterface
{
    /**
     * @param string $field
     * @param null|string $class
     * @param array $options
     * @return TableBuilderInterface
     */
    public function add(string $field, ?string $class = null, array $options = []): TableBuilderInterface;

    public function addColumn(Column $column): TableBuilderInterface;

    /**
     * function(\Doctrine\ORM\QueryBuilder $qb) {}
     */
    public function setConditionCallback(callable $callback): void;
}
