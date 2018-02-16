<?php

namespace Voelkel\DataTablesBundle\Table\Filter;

abstract class AbstractColumnFilter
{
    public $options = [];

    /** @var null|\Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $this->getDefaultOptions(), $options);

        return $this;
    }

    /**
     * @param null|\Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string $field
     * @param string $parameter
     * @param string $value
     */
    abstract public function buildQuery(\Doctrine\ORM\QueryBuilder $qb, $field, $parameter, $value);

    /**
     * @return array
     */
    abstract protected function getDefaultOptions();
}
