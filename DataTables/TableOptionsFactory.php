<?php

namespace Voelkel\DataTablesBundle\DataTables;

class TableOptionsFactory
{
    private $configurationOptions;

    public function __construct($configurationOptions)
    {
        $this->configurationOptions = $configurationOptions;
    }

    /**
     * @return \Voelkel\DataTablesBundle\Table\TableOptions
     */
    public function create()
    {
        $options = new \Voelkel\DataTablesBundle\Table\TableOptions();

        return $options;
    }
}
