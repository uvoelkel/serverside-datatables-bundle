<?php

namespace Voelkel\DataTablesBundle\Twig;

//use Psr\Container\ContainerInterface;

class RuntimeLoader implements \Twig_RuntimeLoaderInterface
{
    private $container;

    public function __construct(/*ContainerInterface*/ $container)
    {
        $this->container = $container;
    }

    public function load($class)
    {
        if (TableRenderer::class === $class) {
            return new TableRenderer($this->container);
        }

        return null;
    }
}
