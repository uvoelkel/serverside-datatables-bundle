<?php

namespace Voelkel\DataTablesBundle\Table\Column;

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

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface|null */
    public $container = null;

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
