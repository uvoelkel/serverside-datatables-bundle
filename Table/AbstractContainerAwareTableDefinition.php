<?php

namespace Voelkel\DataTablesBundle\Table;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractContainerAwareTableDefinition extends AbstractTableDefinition implements ContainerAwareInterface
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface|null */
    protected $container = null;

    /**
     * @inheritdoc
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return null|ContainerInterface
     * @deprecated
     */
    public function getContainer()
    {
        return $this->container;
    }
}
