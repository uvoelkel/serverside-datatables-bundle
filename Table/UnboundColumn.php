<?php

namespace Voelkel\DataTablesBundle\Table;

class UnboundColumn extends CallbackColumn
{
    public function __construct($name, $field, callable $callback, array $options = [])
    {
        $options['unbound'] = true;
        $options['sortable'] = false;

        parent::__construct($name, $field, $callback, $options);
    }
}
