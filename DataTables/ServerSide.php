<?php

namespace Voelkel\DataTablesBundle\DataTables;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Voelkel\DataTablesBundle\Table\AbstractDataTable;
use Voelkel\DataTablesBundle\DataTables\Request as DataTablesRequest;
use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Column\EntitiesColumn;
use Voelkel\DataTablesBundle\Table\Column\EntitiesScalarColumn;
use Voelkel\DataTablesBundle\Table\Column\EntityColumn;
use Voelkel\DataTablesBundle\Table\Column\EntitiesCountColumn;

class ServerSide
{
    /** @var EntityManagerInterface  */
    private $em;

    /** @var DataToStringConverter */
    private $dataConverter;

    /** @var AbstractDataTable */
    private $table;

    /** @var \Voelkel\DataTablesBundle\DataTables\Request */
    private $request;

    /** @var null|string */
    private $identifierField = null;

    /** @var array */
    private $joins = [];

    /**
     * @param EntityManagerInterface $em
     * @param DataToStringConverter $dataToStringConverter
     */
    public function __construct(EntityManagerInterface $em, DataToStringConverter $dataToStringConverter)
    {
        $this->em = $em;
        $this->dataConverter = $dataToStringConverter;
    }

    /**
     * @param AbstractDataTable $table
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function processRequest(AbstractDataTable $table, \Symfony\Component\HttpFoundation\Request $request)
    {
        $this->table = $table;
        $this->request = new DataTablesRequest($request);

        $response = new Response();
        $response->draw = $this->request->getDraw();

        $qb = $this->createQueryBuilder();
        $response->recordsTotal = $this->countTotals($qb);
        $response->recordsFiltered = $this->applyFilterAndCount($qb);
        if (null === $response->recordsFiltered) {
            $response->recordsFiltered = $response->recordsTotal;
        }

        // select entities
        $prefixes = array_merge([$this->table->getPrefix()], $this->table->getJoinPrefixes());
        call_user_func_array([$qb, 'select'], $prefixes);

        // add count
        if ($this->table->getHasScalarColumns()) {
            foreach ($this->table->getColumns() as $column) {
                if ($column instanceof EntitiesCountColumn) {
                    $qb->addSelect('count(' . $column->getEntityPrefix() . ') as ' . $column->getField() . '_count'); // '.' .  $column->getField() .
                } elseif ($column instanceof EntitiesScalarColumn) {
                    $qb->addSelect($column->getOperation() . '(' . $column->getEntityPrefix() . '.' . $column->getEntityField() . ') as ' . $column->getField() . '_' . $column->getOperation()); // '.' .  $column->getField() .
                    $qb->addGroupBy($this->table->getPrefix() . '.id');
                }
            }
        }

        // order
        $this->applyOrder($qb);

        // paginate
        $paginate = clone $qb;
        $paginate->select('distinct(' . $this->table->getPrefix() . '.' . $this->getIdentifierField() . ')');

        // add scalar fields as hidden (todo: clean up this mess)
        if ($this->table->getHasScalarColumns()) {
            foreach ($this->table->getColumns() as $column) {
                if ($column instanceof EntitiesCountColumn) {
                    $paginate->addSelect('count(' . $column->getEntityPrefix() . ') as hidden ' . $column->getField() . '_count'); // '.' .  $column->getField() .
                } elseif ($column instanceof EntitiesScalarColumn) {
                    $paginate->addSelect($column->getOperation() . '(' . $column->getEntityPrefix() . '.' . $column->getEntityField() . ') as hidden ' . $column->getField() . '_' . $column->getOperation()); // '.' .  $column->getField() .
                    $paginate->addGroupBy($this->table->getPrefix() . '.id');
                }
            }
        }

        $paginate->setFirstResult($this->request->getStart())->setMaxResults($this->request->getLength());
        $ids = $paginate->getQuery()->getResult();

        $qb->andWhere($this->table->getPrefix() . '.' . $this->getIdentifierField() . ' in (:ids)')
            ->setParameter('ids', $ids);

        // get result
        $resultCallback = $this->table->getResultCallback();
        if (null !== $resultCallback) {
            call_user_func($resultCallback, $this->table, $qb, $response, $this->dataConverter);
        } else {
            call_user_func(
                ['Voelkel\DataTablesBundle\DataTables\DataBuilder', 'build'],
                $this->table,
                $qb,
                $response,
                $this->dataConverter,
                $this->table->getRowCallback()
            );
        }

        return $response->create();
    }

    /**
     * @return QueryBuilder
     */
    private function createQueryBuilder()
    {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->em->getRepository($this->table->getEntity());

        $qb = $repository->createQueryBuilder($this->table->getPrefix());

        foreach ($this->table->getColumns() as $column) {
            $column->__set('container', $this->table->getContainer());

            /** @var EntityColumn $column */
            if (get_class($column) === 'Voelkel\DataTablesBundle\Table\Column\EntityColumn') {
                $this->joinColumn($qb, $column);
            }

            if ($column instanceof EntitiesColumn) {
                $this->joinColumn($qb, $column);
            }

            if ($column instanceof EntitiesScalarColumn) {
                $this->joinColumn($qb, $column);
            }
        }

        $callback = $this->table->getConditionCallback();
        if (null !== $callback) {
            call_user_func($callback, $qb);
        }

        return $qb;
    }

    private function getIdentifierField()
    {
        if (null !== $this->identifierField) {
            return $this->identifierField;
        }

        $metadata = $this->em->getClassMetadata($this->table->getEntity());
        if ($metadata->isIdentifierComposite) {
            throw new \Exception('composite identifiers are currently not supported.');
        }

        $identifier = $metadata->getIdentifier();
        if (1 !== sizeof($identifier)) {
            throw new \Exception('exactly one identifier expected.');
        }

        $this->identifierField = $identifier[0];
        return $this->identifierField;
    }

    /**
     * @param QueryBuilder $qb
     * @return integer
     */
    private function countTotals(QueryBuilder $qb)
    {
        $qb->select('count(distinct(' . $this->table->getPrefix() . '.' . $this->getIdentifierField() . '))');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param QueryBuilder $qb
     * @return integer|null
     * @throws \Exception
     */
    private function applyFilterAndCount(QueryBuilder $qb)
    {
        if (null === $this->request->getSearchValue() && !$this->table->getHasColumnFilter()) {
            return null;
        }

        $filter = [];
        foreach ($this->table->getColumns() as $column) {
            if ($column instanceof EntitiesCountColumn || true === $column->getOptions()['unbound']) {
                continue;
            }

            $field = $this->getPrefixedField($column);

            if (false !== $column->getOptions()['filter'] || true === $column->getOptions()['filter_empty']) {
                $value = $this->request->getSearchValue($column->getName());

                $empty = null;
                if (true === $column->getOptions()['filter_empty']) {
                    $empty = $this->extractEmptyFilterFromValue($value);
                }

                if (null !== $value || null !== $empty) {
                    $this->applyColumnFilter($column, $value, $qb, $field, $empty);
                    continue;
                }
            }

            $filter[] = $field;
        }

        if (null !== $this->request->getSearchValue()) {
            $where = '(' . join(' like :filter OR ', $filter) . ' like :filter)';
            $qb->andWhere($where);
            $qb->setParameter('filter', '%' . $this->request->getSearchValue() . '%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Column $column
     * @param null|string $value
     * @param QueryBuilder $qb
     * @param string $field
     * @param null|bool $empty
     * @throws \Exception
     */
    private function applyColumnFilter(Column $column, $value, QueryBuilder $qb, $field, $empty)
    {
        if (null === $value && false === $column->getOptions()['filter_empty']) {
            throw new \Exception('this is just wrong');
        }

        $parameter = ':' . $column->getName() . '_filter';

        if ('select' === $column->getOptions()['filter']) {
            if (null !== $value) {
                if (true === $column->getOptions()['multiple']) {
                    $qb->andWhere($field . ' in (' . $parameter . ')');
                    $qb->setParameter($parameter, explode(',', $value));
                } else {
                    $qb->andWhere($field . ' = ' . $parameter);
                    $qb->setParameter($parameter, $value);
                }
            }
        } elseif ('text' === $column->getOptions()['filter']) {
            if (null !== $value) {
                $filterQuery = $column->getOptions()['filter_query'];

                if (false !== strpos($filterQuery, 'value')) {
                    $like = str_replace('value', $value, $filterQuery);
                    $qb->andWhere($field.' like '.$parameter);
                    $qb->setParameter($parameter, '%'.$value.'%');
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

                        $fields[$i] = $field . ' like :' . $param . '_' . $i;
                        $params[$i] = $parameter;
                    }
                    $sql = '(' . join(' ' . $splitOp . ' ', $fields) . ')';

                    $qb->andWhere($sql);
                    foreach ($params as $key => $value) {
                        $qb->setParameter($param . '_' . $key, $value);
                    }
                }
            }
        } elseif (false !== $column->getOptions()['filter']) {
            throw new \Exception(sprintf('invalid filter type "%s"', $column->getOptions()['filter']));
        }

        if (null !== $empty && is_bool($empty) && true === $empty && true === $column->getOptions()['filter_empty']) {
            $qb->andWhere($field . ' is ' . ($empty ? '': 'not ') . 'null');
        }
    }

    /**
     * @param QueryBuilder $qb
     */
    private function applyOrder(QueryBuilder $qb)
    {
        if (0 === sizeof($this->request->getOrder())) {
            foreach ($this->table->getColumns() as $column) {
                if (null !== $column->getOptions()['order']) {
                    $qb->addOrderBy($this->getPrefixedField($column), $column->getOptions()['order']);
                }
            }
            return;
        }

        $columns = $this->request->getColumns();
        foreach ($this->request->getOrder() as $order) {
            $columnIndex = $order['column'];
            $columnName = $columns[$columnIndex]['name'];

            $column = $this->table->getColumns()[$columnName];

            if(true === $column->getOptions()['sortable']) {
                $qb->addOrderBy($this->getPrefixedField($column), $order['dir']);
            }
        }
    }

    /**
     * @param Column $column
     * @return string
     */
    private function getPrefixedField(Column $column)
    {
        if ($column instanceof EntitiesCountColumn) {
            return $column->getField().'_count';
        } elseif ($column instanceof EntitiesScalarColumn) {
            return $column->getField() . '_' . $column->getOperation();
        }elseif ($column instanceof EntityColumn) {
            return $column->getEntityPrefix() . '.' . $column->getEntityField();
        }

        return $this->table->getPrefix() . '.' . $column->getField();
    }

    /**
     * @param QueryBuilder $qb
     * @param EntityColumn $column
     */
    private function joinColumn(QueryBuilder $qb, EntityColumn $column)
    {
        $joins = [];

        $pos = strpos($column->getField(), '.');
        if (false !== $pos) {
            $fields = $column->getField();
            $prefix = '';

            while (false !== $pos) {
                $join = [empty($prefix) ? $this->table->getPrefix() : $prefix];

                $field = substr($fields, 0, $pos);
                $prefix .= (empty($prefix) ? '' : '_') . EntityColumn::createEntityPrefix($field);

                array_push($join, $field, $prefix);
                $joins[join('.', $join)] = $join;

                $fields = substr($fields, $pos + 1);
                $pos = strpos($fields, '.');

                if (false === $pos && 0 < strlen($fields)) {
                    $pos = strlen($fields);
                }
            }
        } else {
            $join = [
                $this->table->getPrefix(),
                $column->getField(),
                $column->getEntityPrefix()
            ];
            $joins[join('.', $join)] = $join;
        }

        foreach ($joins as $key => $join) {
            if (!isset($this->joins[$key])) {
                $qb->leftJoin($join[0] . '.' . $join[1], $join[2]);
                $this->joins[$key] = $join;
            }
        }
    }

    /**
     * @param null|string $value
     * @return bool|null
     * @throws \Exception
     */
    private function extractEmptyFilterFromValue(&$value)
    {
        if (null === $value) {
            return null;
        }

        if ('|empty=true' === substr($value, -11)) {
            $value = substr($value, 0, -11);
            $empty = true;
        } elseif ('|empty=false' === substr($value, -12)) {
            $value = substr($value, 0, -12);
            $empty = false;
        } else {
            throw new \Exception(sprintf('invalid filter value "%s"', $value));
        }

        if (empty($value)) {
            $value = null;
        }

        return $empty;
    }
}
