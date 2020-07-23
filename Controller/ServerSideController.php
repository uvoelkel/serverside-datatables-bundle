<?php

namespace Voelkel\DataTablesBundle\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ServerSideController extends AbstractController
{
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function listAction($table, Request $request)
    {
        return $this->list($table, $request);
    }

    /**
     * @param string $table
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function list($table, Request $request)
    {
        if (class_exists($table)) {
            $table = new $table();
        } elseif ($this->has($table)) {
            $table = $this->get($table);
        } else {
            throw new \Exception(sprintf('table definition class or service "%s" not found.', $table));
        }

        if (
            $request->query->has('parameters') &&
            is_array($request->query->get('parameters'))
        ) {
            $table->setRequestParameters($request->query->get('parameters'));
        }

        /** @var \Voelkel\DataTablesBundle\Table\AbstractDataTable $table */
        $table->setContainer($this->container);

        return $this->get('serverside_datatables')->processRequest($table, $request);
    }
}
