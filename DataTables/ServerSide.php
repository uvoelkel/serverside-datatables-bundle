<?php

namespace Voelkel\DataTablesBundle\DataTables;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Voelkel\DataTablesBundle\Table\AbstractTableDefinition;
use Voelkel\DataTablesBundle\DataTables\Request as DataTablesRequest;
use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Column\EntitiesColumn;
use Voelkel\DataTablesBundle\Table\Column\EntityColumn;
use Voelkel\DataTablesBundle\Table\Column\EntitiesCountColumn;

class ServerSide
{
    /** @var EntityManagerInterface  */
    private $em;

    /** @var AbstractTableDefinition */
    private $table;

    /** @var \Voelkel\DataTablesBundle\DataTables\Request */
    private $request;

    private $joins = [];

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param AbstractTableDefinition $table
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function processRequest(AbstractTableDefinition $table, \Symfony\Component\HttpFoundation\Request $request)
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

        foreach ($this->table->getColumns() as $column) {
            // add count
            if ($this->table->getHasCountColumns() && $column instanceof EntitiesCountColumn) {
                //$this->joinColumn($qb, $column);
                $qb->addSelect('count(' . $column->getEntityPrefix() . ') as ' . $column->getField() . '_count'); // '.' .  $column->getField() .
            }
        }

        if ($this->table->getHasCountColumns()) {
            $qb->groupBy($this->table->getPrefix() . '.id');
        }


        // order
        $this->applyOrder($qb);

        // paginate
        $qb->setFirstResult($this->request->getStart())
            ->setMaxResults($this->request->getLength());

        // get result
        $resultCallback = $this->table->getResultCallback();
        if (null !== $resultCallback) {
            call_user_func($resultCallback, $this->table, $qb, $response);
        } else {
            call_user_func(['Voelkel\DataTablesBundle\DataTables\DataBuilder', 'build'], $this->table, $qb, $response);
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
            /** @var EntityColumn $column */
            if (get_class($column) === 'Voelkel\DataTablesBundle\Table\Column\EntityColumn') {
                $this->joinColumn($qb, $column);
            }

            if ($column instanceof EntitiesColumn) {
                $this->joinColumn($qb, $column);
            }
        }

        $callback = $this->table->getConditionCallback();
        if (null !== $callback) {
            call_user_func($callback, $qb);
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @return integer
     * @throws \Exception
     */
    private function countTotals(QueryBuilder $qb)
    {
        $countColumn = null;
        foreach ($this->table->getColumns() as $column) {
            if (get_class($column) === 'Voelkel\DataTablesBundle\Table\Column\Column') {
                $countColumn = $this->table->getPrefix() . '.' . $column->getField();
                break;
            }
        }

        if (null === $countColumn) {
            throw new \Exception('no countable column found.');
        }

        $qb->select('count(' . $countColumn . ')');
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
                $qb->andWhere($field . ' like ' . $parameter);
                $qb->setParameter($parameter, '%' . $value . '%');
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
            return $column->getField() . '_count';
        } elseif ($column instanceof EntityColumn) {
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
        $join = $this->table->getPrefix() . '.' . $column->getField() . '.' . $column->getEntityPrefix();
        if (!in_array($join, $this->joins)) {
            $qb->leftJoin($this->table->getPrefix() . '.' . $column->getField(), $column->getEntityPrefix());
            $this->joins[] = $join;
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
