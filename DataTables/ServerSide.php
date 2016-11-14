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
     * @return \Symfony\Component\HttpFoundation\JsonResponse
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

        $dql = null;

        if (AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
            call_user_func_array([$qb, 'select'], $prefixes);
        } else {
            $dql = 'SELECT ' . join(', ', $prefixes) . ' ';
        }


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
        $qb = $this->applyOrder($qb);

        // paginate
        if (AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
            $paginate = clone $qb;
            $paginate->select('distinct('.$this->table->getPrefix().'.'.$this->getIdentifierField().')');
        } else {
            $paginate = $qb;
            $paginate = 'SELECT distinct('.$this->table->getPrefix().'.'.$this->getIdentifierField().') ' . $qb;
        }

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

        if (AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
            $paginate->setFirstResult($this->request->getStart())->setMaxResults($this->request->getLength());
            $ids = $paginate->getQuery()->getResult();
        } else {
            $qry = $this->em->createQuery($paginate);
            $qry->setFirstResult($this->request->getStart())->setMaxResults($this->request->getLength());
            $ids = $qry->getArrayResult();
        }


        if (AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
            $qb->andWhere($this->table->getPrefix().'.'.$this->getIdentifierField().' in (:ids)')
                ->setParameter('ids', $ids);
        } else {

            $tmp = [];
            foreach ($ids as $id) {
                $tmp[] = $id[1];
            }

            $ids = $this->table->getPrefix().'.'.$this->getIdentifierField().' in (' . join(',' , $tmp) . ')';
            $qb = str_replace('WHERE 1 = 1', 'WHERE ' . $ids, $qb);
            $dql .= $qb;
        }

        if (AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
            $query = $qb->getQuery();
        } else {
            $query = $this->em->createQuery($dql);
        }

        // get result
        $resultCallback = $this->table->getResultCallback();
        if (null !== $resultCallback) {
            call_user_func($resultCallback, $this->table, $query, $response, $this->dataConverter);
        } else {
            call_user_func(
                ['Voelkel\DataTablesBundle\DataTables\DataBuilder', 'build'],
                $this->table,
                $query,
                $response,
                $this->dataConverter,
                $this->table->getRowCallback()
            );
        }

        return $response->create();
    }

    /**
     * @return QueryBuilder|string
     */
    private function createQueryBuilder()
    {
        if (AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
            /** @var \Doctrine\ORM\EntityRepository $repository */
            $repository = $this->em->getRepository($this->table->getEntity());
            $qb = $repository->createQueryBuilder($this->table->getPrefix());
        } else {
            $qb = 'FROM ' . $this->table->getEntity() . ' ' . $this->table->getPrefix() . ' ';
        }

        foreach ($this->table->getColumns() as $column) {
            $column->__set('container', $this->table->getContainer());

            /** @var EntityColumn $column */
            if (get_class($column) === 'Voelkel\DataTablesBundle\Table\Column\EntityColumn') {
                $qb = $this->joinColumn($qb, $column);
            }

            if ($column instanceof EntitiesColumn) {
                $qb = $this->joinColumn($qb, $column);
            }

            if ($column instanceof EntitiesScalarColumn) {
                $qb = $this->joinColumn($qb, $column);
            }
        }

        if (AbstractDataTable::QUERY_MODE_DQL === $this->table->getQueryMode()) {
            $qb .= 'WHERE 1 = 1 ';
        }

        $callback = $this->table->getConditionCallback();
        if (null !== $callback) {

            if (AbstractDataTable::QUERY_MODE_DQL === $this->table->getQueryMode()) {
                $qb = call_user_func($callback, $qb);
            } else {
                call_user_func($callback, $qb);
            }

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
     * @param QueryBuilder|string $qb
     * @return integer
     */
    private function countTotals($qb)
    {
        if (AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
            $qb->select('count(distinct('.$this->table->getPrefix().'.'.$this->getIdentifierField().'))');
            return $qb->getQuery()->getSingleScalarResult();
        } else {
            $dql = 'SELECT count(distinct('.$this->table->getPrefix().'.'.$this->getIdentifierField().')) ' . $qb;
            return $this->em->createQuery($dql)->getSingleScalarResult();
        }
    }

    /**
     * @param QueryBuilder|string $qb
     * @return integer|null
     * @throws \Exception
     */
    private function applyFilterAndCount($qb)
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
            if (AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
                $where = '(' . join(' like :filter OR ', $filter) . ' like :filter)';
                $qb->andWhere($where);
                $qb->setParameter('filter', '%' . $this->request->getSearchValue() . '%');
            } else {
                $value = '%' . $this->request->getSearchValue() . '%';
                $qb .= ' AND ' . sprintf('(' . join(' like %s OR ', $filter) . ' like %s)', $value, $value);
            }
        }

        if (AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
            return $qb->getQuery()->getSingleScalarResult();
        } else {
            $dql = 'SELECT count(distinct('.$this->table->getPrefix().'.'.$this->getIdentifierField().')) ' . $qb;
            return $this->em->createQuery($dql)->getSingleScalarResult();
        }
    }

    /**
     * @param Column $column
     * @param null|string $value
     * @param QueryBuilder|string $qb
     * @param string $field
     * @param null|bool $empty
     * @throws \Exception
     */
    private function applyColumnFilter(Column $column, $value, $qb, $field, $empty)
    {
        if (null === $value && false === $column->getOptions()['filter_empty']) {
            throw new \Exception('this is just wrong');
        }

        $parameter = ':' . $column->getName() . '_filter';


        if ($column->getOptions()['filter'] instanceof \Voelkel\DataTablesBundle\Table\Filter\AbstractColumnFilter) {
            if (
                isset($column->getOptions()['filter']->options['field']) &&
                null !== $column->getOptions()['filter']->options['field']
            ) {
                $field = $this->table->getPrefix() . '.' . $column->getOptions()['filter']->options['field'];
            }

            if ($qb instanceof QueryBuilder && AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
                $column->getOptions()['filter']->setContainer($this->table->getContainer());
            $column->getOptions()['filter']->buildQuery($qb, $field, $parameter, $value);
            } elseif (is_string($qb) && AbstractDataTable::QUERY_MODE_DQL === $this->table->getQueryMode()) {

                // todo
                //$column->getOptions()['filter']->buildQuery($qb, $field, $parameter, $value);

            } else {
                throw new \Exception();
            }

        } elseif (false !== $column->getOptions()['filter']) {
            throw new \Exception(sprintf('invalid filter type "%s"', $column->getOptions()['filter']));
        }

        if (null !== $empty && is_bool($empty) && true === $empty && true === $column->getOptions()['filter_empty']) {

            if ($qb instanceof QueryBuilder && AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
                $qb->andWhere($field . ' is ' . ($empty ? '': 'not ') . 'null');
            } elseif (is_string($qb) && AbstractDataTable::QUERY_MODE_DQL === $this->table->getQueryMode()) {
                $qb .= ' AND ' . $field . ' IS ' . ($empty ? '': 'NOT ') . 'NULL';
            } else {
                throw new \Exception();
            }

        }
    }

    /**
     * @param QueryBuilder|string $qb
     */
    private function applyOrder($qb)
    {
        if (0 === sizeof($this->request->getOrder())) {
            $count = 0;
            foreach ($this->table->getColumns() as $column) {
                if (null !== $column->getOptions()['order']) {
                    if ($qb instanceof QueryBuilder && AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
                        $qb->addOrderBy($this->getPrefixedField($column), $column->getOptions()['order']);
                    } else {
                        $qb .= (0 === $count ? ' ORDER BY ' : ' , ');
                        $qb .= $this->getPrefixedField($column) . ' ' . $column->getOptions()['order'];
                    }
                    $count++;
                }
            }
            return $qb;
        }

        $count = 0;
        $columns = $this->request->getColumns();
        foreach ($this->request->getOrder() as $order) {
            $columnIndex = $order['column'];
            $columnName = $columns[$columnIndex]['name'];

            $column = $this->table->getColumns()[$columnName];

            if(true === $column->getOptions()['sortable']) {
                if ($qb instanceof QueryBuilder && AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
                    $qb->addOrderBy($this->getPrefixedField($column), $order['dir']);
                } else {
                    $qb .= (0 === $count ? ' ORDER BY ' : ' , ');
                    $qb .= $this->getPrefixedField($column) . ' ' . $column->getOptions()['order'];
                }
                $count++;
            }
        }

        return $qb;
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
     * @param QueryBuilder|string $qb
     * @param EntityColumn $column
     */
    private function joinColumn($qb, EntityColumn $column)
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

                if ($qb instanceof QueryBuilder && AbstractDataTable::QUERY_MODE_QUERY_BUILDER === $this->table->getQueryMode()) {
                    $qb->leftJoin($join[0].'.'.$join[1], $join[2]);
                } elseif (is_string($qb) && AbstractDataTable::QUERY_MODE_DQL === $this->table->getQueryMode()) {
                    $qb .= ' LEFT JOIN ' . $join[0] . '.' . $join[1] . ' ' . $join[2] . ' ';
                } else {
                    throw new \Exception();
                }

                $this->joins[$key] = $join;
            }
        }

        return $qb;
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
