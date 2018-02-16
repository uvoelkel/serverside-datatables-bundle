<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

class ChoiceFilter extends AbstractColumnFilter
{
    public function __construct(array $options = [])
    {
        if (0 !== sizeof($options)) {
            @trigger_error(
                'Passing filter options to the constructor is deprecated. Use the "filter_options" column option instead.',
                E_USER_DEPRECATED
            );
        }

        $this->options = array_merge([
            'choices' => [],
            'multiple' => false,
            'expanded' => false,
        ], $options);
    }

    protected function getDefaultOptions()
    {
        return [
            'choices' => [],
            'multiple' => false,
            'expanded' => false,
        ];
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
