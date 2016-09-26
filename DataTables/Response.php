<?php

namespace Voelkel\DataTablesBundle\DataTables;

class Response
{
    /**
     * @var int
     */
    public $draw = -1;

    /**
     * @var int
     */
    public $recordsTotal = -1;

    /**
     * @var int
     */
    public $recordsFiltered = -1;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function create()
    {
        $result = [
            'draw' => $this->draw,
            'recordsTotal' => $this->recordsTotal,
            'recordsFiltered' => $this->recordsFiltered,
            'data' => $this->data,
        ];

        return new \Symfony\Component\HttpFoundation\JsonResponse($result);
    }
}
