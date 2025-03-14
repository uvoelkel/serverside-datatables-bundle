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
     * function(\Doctrine\ORM\QueryBuilder $qb): void {}
     */
    public function setConditionCallback(callable $callback): TableBuilderInterface;

    /**
     * function(\Doctrine\ORM\QueryBuilder $qb): void {}
     */
    public function setOrderCallback(callable $callback): TableBuilderInterface;

    /**
     * function(TableBuilderInterface, \Doctrine\ORM\QueryBuilder, \Voelkel\DataTablesBundle\DataTables\Response, ßVoelkel\DataTablesBundle\DataTables\DataToStringConverter): array {}
     */
    public function setResultCallback(callable $callback): TableBuilderInterface;

    /**
     * function($entity, TableBuilderInterface): array {}
     */
    public function setRowCallback(callable $callback): TableBuilderInterface;
}
