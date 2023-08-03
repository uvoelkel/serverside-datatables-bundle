<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

class DateFilter extends AbstractColumnFilter
{
    public function getDefaultOptions(): array
    {
        return [
            'field' => null,
            'operator' => 'like',
            'query' => '%value%',
        ];
    }

    public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value): void
    {
        if (null === $value) {
            return;
        }

        $filterQuery = $this->options['query'];
        $filterOperator = $this->options['operator'];

        $like = str_replace('value', $value, $filterQuery);
        $qb->andWhere($field.' ' . $filterOperator . ' '.$parameter);
        $qb->setParameter($parameter, $like);
    }
}
