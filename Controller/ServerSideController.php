<?php

namespace Voelkel\DataTablesBundle\Controller;

use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Voelkel\DataTablesBundle\DataTables\ServerSide;
use Voelkel\DataTablesBundle\Table\UserAwareTableInterface;

class ServerSideController extends AbstractController
{
    private ?TokenStorageInterface $tokenStorage;
    private ?AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        ContainerInterface $container,
        ?TokenStorageInterface $tokenStorage,
        ?AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->setContainer($container);
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
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
    public function list($table, ServerSide $serverSide, Request $request)
    {
        if ($this->container->has($table . '.public')) {
            $table = $this->container->get($table . '.public');
        } elseif (class_exists($table)) {
            $table = new $table();
        } elseif ($this->has($table)) {
            $table = $this->get($table);
        } else {
            throw new \Exception(sprintf('table definition class or service "%s" not found.', $table));
        }

        if (
            $request->query->has('parameters') &&
            is_array($request->query->all('parameters'))
        ) {
            $table->setRequestParameters($request->query->all('parameters'));
        }

        if ($table instanceof UserAwareTableInterface) {
            $table->setUser($this->tokenStorage?->getToken()?->getUser());
            $table->setAuthorizationChecker($this->authorizationChecker);
        }

        /** @var \Voelkel\DataTablesBundle\Table\AbstractDataTable $table */
        $table->setContainer($this->container);

        return $serverSide->processRequest($table, $request);
    }
}
