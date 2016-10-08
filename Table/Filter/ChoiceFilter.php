<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

class ChoiceFilter extends AbstractColumnFilter
{
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'choices' => [],
            'multiple' => false,
            'expanded' => false,
        ], $options);
    }

    public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value)
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
