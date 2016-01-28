<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class CallbackColumn extends Column
{
    /**
     * @param string $name
     * @param string $field
     * @param callable $callback
     * @param array $options
     */
    public function __construct($name, $field, callable $callback, array $options = [])
    {
        $options['format_data_callback'] = $callback;

        parent::__construct($name, $field, $options);
    }
}
