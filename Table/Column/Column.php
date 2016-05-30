<?php

namespace Voelkel\DataTablesBundle\Table\Column;

class Column
{
    /** @var string */
    private $name;

    /** @var string */
    private $field;

    /** @var \Voelkel\DataTablesBundle\Table\AbstractDataTable */
    private $table;

    const FILTER_NONE = false;
    const FILTER_TEXT = 'text';
    const FILTER_SELECT = 'select';

    const ORDER_ASCENDING = 'asc';
    const ORDER_DESCENDING = 'desc';

    /** @var array */
    private $options = [
        'sortable' => true,
        'searchable' => true,
        'filter' => false, // false|'text'|'select' todo: |'bool'|'date'|'datetime'|'date_range'|'datetime_range'|\Voelkel\DataTablesBundle\Table\Filter\FilterInterface
        'filter_choices' => [], // 'filter' => 'select' only
        'filter_query' => '%f%',
        'filter_empty' => false, // add a checkbox to filter empty resp null values
        'multiple' => false,
        'expanded' => false,
        'format_data_callback' => null, // function ($data, $entity, Column $column) {}
        'unbound' => false,
        'order' => null, // null|'asc'|'desc'
        'label' => null, // null|string|false
        'abbr' => null,
    ];

    private $fields = [];

    public function __get($name)
    {
        if (!isset($this->fields[$name])) {
            return null;
        }

        if ('container' === $name) {
            @trigger_error(
                'The "container" property is deprecated. Use "$this->container" in column callbacks instead.',
                E_USER_DEPRECATED
            );
        }

        return $this->fields[$name];
    }

    public function __set($name, $value)
    {
        if ('container' !== $name) {
            throw new \Exception('magic setter is for deprecated fields only.');
        }

        $this->fields[$name] = $value;
    }

    /**
     * @param string $name
     * @param string $field
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
     * @return \Voelkel\DataTablesBundle\Table\AbstractDataTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param \Voelkel\DataTablesBundle\Table\AbstractDataTable $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLabel()
    {
        if (null === $this->options['label']) {
            return $this->name;
        } elseif (is_string($this->options['label'])) {
            return $this->options['label'];
        } elseif (false === $this->options['label']) {
            return '';
        } else {
            throw new \Exception('invalid label option: ' . $this->options['label']);
        }
    }
}
