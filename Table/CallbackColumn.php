<?php

namespace Voelkel\DataTablesBundle\Table;

class CallbackColumn extends Column
{
    public function __construct($name, $field, callable $callback, array $options = [])
    {
        $options['format_data_callback'] = $callback;

        parent::__construct($name, $field, $options);
    }
}
