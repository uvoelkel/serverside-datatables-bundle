<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class UnboundColumn extends CallbackColumn
{
    public function __construct($name, callable $callback, array $options = [])
    {
        if (isset($options['sortable']) && true === $options['sortable']) {
            throw new \Exception('sortable = true is not allowed for UnboundColumn');
        }

        $options['unbound'] = true;
        $options['sortable'] = false;

        parent::__construct($name, $name, $callback, $options);
    }
}
