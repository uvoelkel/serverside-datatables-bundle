<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

class FilterGroup extends AbstractColumnFilter
{
    private $filters = [];

    protected function getDefaultOptions()
    {
        return [];
    }

    public function addFilter(AbstractColumnFilter $filter)
    {
        $this->filters[] = $filter;
    }

    public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value)
    {
        foreach ($this->filters as $filter) {
            $filter->buildQuery($qb, $field, $parameter, $value);
        }
    }
}
