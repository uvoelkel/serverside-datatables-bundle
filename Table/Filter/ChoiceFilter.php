<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

class ChoiceFilter extends AbstractColumnFilter
{
    public function getDefaultOptions(): array
    {
        return [
            'choices' => [],
            'multiple' => false,
            'expanded' => false,
        ];
    }

    public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value): void
    {
        if (null !== $value) {
            if (true === $this->options['multiple']) {
                $qb->andWhere($field . ' in (' . $parameter . ')');
                $qb->setParameter($parameter, explode(',', $value));
            } else {
                $qb->andWhere($field . ' = ' . $parameter);
                $qb->setParameter($parameter, $value);
            }
        }
    }
}
