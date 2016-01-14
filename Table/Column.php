<?php

namespace Voelkel\DataTablesBundle\Table;

class Column
{
    /** @var string */
    private $name;

    /** @var string */
    private $field;

    /** @var array */
    private $options = [
        'sortable' => true,
        'searchable' => true,
        'filter' => false, // false|'text'|'select'
        'filter_choices' => [],
        'filter_query' => '%f%',
        'multiple' => false,
        'expanded' => false,
        'format_data_callback' => null, // function ($data, $column) {}
        'unbound' => false,
        'order' => null, // null|'asc'|'desc'
    ];

    /**
     * @param $name
     * @param $field
     * @param array $options
     */
    public function __construct($name, $field, array $options = [])
    {
        $this->name = $name;
        $this->field = $field;

        $this->options = array_merge($this->options, $options);

        if (false !== $this->options['filter']) {
            $this->options['searchable'] = true;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (isset($this->options['label'])) {
            return $this->options['label'];
        }

        return $this->name;
    }
}
