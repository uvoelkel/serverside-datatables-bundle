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
    public function add(string $field, $class = null, $options = []);

    /**
     * @param Column $column
     * @return TableBuilderInterface
     */
    public function addColumn(Column $column);

    /**
     * @param callable $callback
     *
     * function(\Doctrine\ORM\QueryBuilder $qb) {}
     */
    public function setConditionCallback(callable $callback);
}
