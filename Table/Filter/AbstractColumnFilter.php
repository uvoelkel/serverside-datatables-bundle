<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

abstract class AbstractColumnFilter
{
    public $options = [];

    /** @var null|\Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    /**
     * @param null|\Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value)
    {

    }
}
