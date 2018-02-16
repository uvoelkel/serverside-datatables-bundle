<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

class TextFilter extends AbstractColumnFilter
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
            'field' => null,
            'filter_operator' => 'like',
            'filter_query' => '%value%', // [%]value|split( |and)[%]
        ], $options);
    }

    protected function getDefaultOptions()
    {
        return [
            'field' => null,
            'filter_operator' => 'like',
            'filter_query' => '%value%', // [%]value|split( |and)[%]
        ];
    }

    public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value)
    {
        if (null !== $value) {
            $filterQuery = $this->options['filter_query'];
            $filterOperator = $this->options['filter_operator'];


            if (false !== strpos($filterQuery, 'value')) {
                $like = str_replace('value', $value, $filterQuery);
                $qb->andWhere($field.' ' . $filterOperator . ' '.$parameter);
                $qb->setParameter($parameter, $like);
            } elseif (false !== strpos($filterQuery, 'split(')) {
                $splitStart = strpos($filterQuery, 'split(');
                $splitEnd = strpos($filterQuery, ')', $splitStart) + 1;
                $split = substr($filterQuery, $splitStart, $splitEnd - $splitStart);

                $splitSettings = str_replace('split(', '', $split);
                $splitSettings = str_replace(')', '', $splitSettings);
                $splitSettings = explode('|', $splitSettings);

                $splitChar = $splitSettings[0];
                $parts = explode($splitChar, $value);

                $splitOp = $splitSettings[1];

                $fields = [];
                $params = [];

                $param = str_replace('.', '_', $field);

                for ($i = 0; $i < sizeof($parts); $i++) {
                    $parameter = str_replace($split, $parts[$i], $filterQuery);
                    if (0 === strlen(str_replace('%', '', $parameter))) {
                        continue;
                    }

                    $fields[$i] = $field . ' ' . $filterOperator . ' :' . $param . '_' . $i;
                    $params[$i] = $parameter;
                }
                $sql = '(' . join(' ' . $splitOp . ' ', $fields) . ')';

                $qb->andWhere($sql);
                foreach ($params as $key => $value) {
                    $qb->setParameter($param . '_' . $key, $value);
                }
            }
        }
    }
}
