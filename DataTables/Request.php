<?php

namespace Voelkel\DataTablesBundle\DataTables;

class Request
{
    private $request;

    public function __construct(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return int|null
     */
    public function getDraw()
    {
        return $this->request->query->get('draw');
    }

    /**
     * @return int|null
     */
    public function getStart()
    {
        return $this->request->query->get('start');
    }

    /**
     * @return int|null
     */
    public function getLength()
    {
        return $this->request->query->get('length');
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->request->query->get('columns', array());
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->request->query->get('order', array());
    }

    /**
     * @param null|int|string $column
     * @return array
     */
    public function getSearch($column = null)
    {
        if (null === $column) {
            return $this->request->query->get('search', [
                'regex' => 'false',
                'value' => '',
            ]);
        } else  {
            $col = null;
            if (is_numeric($column)) {
                $columns = $this->getColumns();
                $col = $columns[(int)$column];
            } elseif (is_string($column)) {
                foreach ($this->getColumns() as $idx => $val) {
                    if ($val['name'] === $column) {
                        $col = $val;
                        break;
                    }
                }
            }

            if (null === $col) {
                return [
                    'regex' => 'false',
                    'value' => '',
                ];
            }

            return [
                'regex' => $col['search']['regex'],
                'value' => $col['search']['value'],
            ];
        }
    }

    /**
     * @param null|int|string $column
     * @return null|string
     */
    public function getSearchValue($column = null)
    {
        $search = $this->getSearch($column);
        return 0 < strlen($search['value']) ? $search['value'] : null;
    }
}
