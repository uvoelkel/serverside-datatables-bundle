<?php

namespace Voelkel\DataTablesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Voelkel\DataTablesBundle\Table\AbstractContainerAwareTableDefinition;
use Voelkel\DataTablesBundle\Table\AbstractTableDefinition;

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
        if (class_exists($table)) {
            $table = new $table();
        } elseif ($this->has($table)) {
            $table = $this->get($table);
        } else {
            throw new \Exception(sprintf('table definition class or service "%s" not found.', $table));
        }

        /** @var AbstractTableDefinition|AbstractContainerAwareTableDefinition $table */
        if ($table instanceof AbstractContainerAwareTableDefinition) {
            $table->setContainer($this->container);
        }

        return $this->get('voelkel.datatables')->processRequest($table, $request);
    }
}
