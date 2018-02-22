<?php

namespace Voelkel\DataTablesBundle\Twig;

use Twig\Compiler;

class TableThemeNode extends \Twig_Node
{
    public function __construct(\Twig_Node $table, \Twig_Node $resources, int $lineno, string $tag = null)
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
