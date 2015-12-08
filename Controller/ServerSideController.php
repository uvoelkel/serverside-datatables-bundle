<?php

namespace Voelkel\DataTablesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ServerSideController extends Controller
{
    /**
     * @param string $table
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function listAction($table, Request $request)
    {
        if (!class_exists($table)) {
            throw new \Exception(sprintf('table definition class "%s" not found.', $table));
        }

        return $this->get('voelkel.datatables')->processRequest(new $table(), $request);
    }
}
