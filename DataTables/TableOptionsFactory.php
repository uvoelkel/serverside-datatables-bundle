<?php

namespace Voelkel\DataTablesBundle\DataTables;

class TableOptionsFactory
{
    private $configuration;

    private $defaultLocale;

    public function __construct($configuration, $defaultLocale)
    {
        $this->configuration = $configuration;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return \Voelkel\DataTablesBundle\Table\TableOptions
     */
    public function create()
    {
        $options = new \Voelkel\DataTablesBundle\Table\TableOptions();

        return $options;
    }

    public function getDefaultOptions()
    {
        $default = \Voelkel\DataTablesBundle\Table\TableOptions::getDefaultOptions($this->defaultLocale);
        $default->merge($this->configuration['table_options']);
        return $default;
    }
}
