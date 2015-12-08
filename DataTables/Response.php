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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create()
    {
        $result = [
            'draw' => $this->draw,
            'recordsTotal' => $this->recordsTotal,
            'recordsFiltered' => $this->recordsFiltered,
            'data' => $this->data,
        ];

        $response = new \Symfony\Component\HttpFoundation\Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
