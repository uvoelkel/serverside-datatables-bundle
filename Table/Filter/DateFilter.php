<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

class DateFilter extends AbstractColumnFilter
{
    protected function getDefaultOptions()
    {
        return [
            'field' => null,
            'filter_operator' => 'like',
            'filter_query' => '%value%',
        ];
    }

    public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value)
    {
        if (null === $value) {
            return;
        }

        $filterQuery = $this->options['filter_query'];
        $filterOperator = $this->options['filter_operator'];

        $like = str_replace('value', $value, $filterQuery);
        $qb->andWhere($field.' ' . $filterOperator . ' '.$parameter);
        $qb->setParameter($parameter, $like);
    }
}
