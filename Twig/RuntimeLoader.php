<?php

namespace Voelkel\DataTablesBundle\Twig;

class RuntimeLoader implements \Twig\RuntimeLoader\RuntimeLoaderInterface
{
    private $container;

    public function __construct(/*ContainerInterface*/ $container)
    {
        $this->container = $container;
    }

    public function load($class): ?object
    {
        if (TableRenderer::class === $class) {
            return new TableRenderer($this->container);
        }

        return null;
    }
}
