<?php

namespace Voelkel\DataTablesBundle\Twig;

use Twig\Compiler;

class TableThemeNode extends \Twig\Node\Node
{
    public function __construct(\Twig\Node\Node $table, \Twig\Node\Node $resources, int $lineno, string $tag = null)
    {
        parent::__construct(['table' => $table, 'resources' => $resources], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$this->env->getRuntime(')
            ->string(TableRenderer::class)
            ->raw(')->setThemes(')
            ->subcompile($this->getNode('table'))
            ->raw(', ')
            ->subcompile($this->getNode('resources'))
            ->raw(");\n");
    }
}
