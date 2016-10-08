<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

abstract class AbstractColumnFilter
{
    public $options = [];

    public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value)
    {

    }

}
