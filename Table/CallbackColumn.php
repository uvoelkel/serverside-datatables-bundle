<?php

namespace Voelkel\DataTablesBundle\Table;

class CallbackColumn extends Column
{
    private $callback;

    public function __construct($name, $field, callable $callback, array $options = [])
    {
        $options['format_data_callback'] = $callback;

        $this->callback = $callback;

        parent::__construct($name, $field, $options);
    }

    public function getCallback()
    {
        return $this->callback;
    }
}
