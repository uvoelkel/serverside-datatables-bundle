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
        'filter' => false, // false|'text'|'select' todo: |'date'|'datetime'|'date_range'|'datetime_range'
        'filter_choices' => [], // 'filter' => 'select' only
        'filter_query' => '%f%',
        'filter_empty' => false, // add a checkbox to filter empty resp null values
        'multiple' => false,
        'expanded' => false,
        'format_data_callback' => null, // function ($data, $object, Column $column) {}
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
