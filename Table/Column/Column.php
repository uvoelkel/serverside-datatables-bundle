<?php

namespace Voelkel\DataTablesBundle\Table\Column;

use Voelkel\DataTablesBundle\Table\Filter\ChoiceFilter;
use Voelkel\DataTablesBundle\Table\Filter\TextFilter;

class Column
{
    /** @var string */
    private $name;

    /** @var string */
    private $field;

    /** @var \Voelkel\DataTablesBundle\Table\AbstractDataTable */
    private $table;

    public $filterRendered = false;

    /** @var \Voelkel\DataTablesBundle\Table\Filter\AbstractColumnFilter[] */
    private $filterInstances = [];

    private $filterBlockPrefixes = null;

    /** @deprecated  */
    const FILTER_NONE = false;
    /** @deprecated  */
    const FILTER_TEXT = 'text';
    /** @deprecated  */
    const FILTER_SELECT = 'select';

    const ORDER_ASCENDING = 'asc';
    const ORDER_DESCENDING = 'desc';

    /** @var array */
    private $options = [
        'visible' => true,
        'sortable' => true,
        'searchable' => true,
        'width' => null,
        'filter' => false, // false|'text'|'select' todo: |'bool'|'date'|'datetime'|'date_range'|'datetime_range'|\Voelkel\DataTablesBundle\Table\Filter\FilterInterface
        'filter_options' => [
            'popover' => false,
        ],
        'filter_choices' => [], // 'filter' => 'select' only
        'filter_query' => '%value%', // [%]value|split( |and)[%]
        'filter_attr' => [],
        'filter_empty' => false, // add a checkbox to filter empty resp null values
        'multiple' => false,
        'expanded' => false,
        'format_data_callback' => null, // function ($data, $entity, Column $column) {}
        'unbound' => false,
        'order' => null, // null|'asc'|'desc'
        'label' => null, // null|string|false
        'placeholder' => null, // null|string|false
        'abbr' => null,
        'responsive_priority' => null,
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

        if (is_object($this->options['filter'])) {
            @trigger_error(
                'Using filter objects for option "filter" is deprecated. Use "FilterClass::class" instead.',
                E_USER_DEPRECATED
            );

            if ($this->options['filter'] instanceof \Voelkel\DataTablesBundle\Table\Filter\AbstractColumnFilter) {
                $this->options['filter_options'] = array_merge($this->options['filter']->options, $this->options['filter_options']);
            }
        }

        if (self::FILTER_TEXT === $this->options['filter']) {
            $this->options['filter'] = TextFilter::class;
            $this->options['filter_options'] = array_merge([
                'filter_operator' => 'like',
                'filter_query' => $this->options['filter_query'],
            ], $this->options['filter_options']);
        } elseif (self::FILTER_SELECT === $this->options['filter']) {
            $this->options['filter'] = ChoiceFilter::class;
            $this->options['filter_options'] = array_merge([
                'choices' => $this->options['filter_choices'],
                'multiple' => $this->options['multiple'],
                'expanded' => $this->options['expanded'],
            ], $this->options['filter_options']);
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
     * @return null|string|\Voelkel\DataTablesBundle\Table\Filter\AbstractColumnFilter
     */
    public function getFilter()
    {
        return false === $this->options['filter'] ? null : $this->options['filter'];
    }


    public function getFilterOptions()
    {
        return $this->options['filter_options'];
    }

    public function getFilterInstances()
    {
        if (0 !== sizeof($this->filterInstances)) {
            return $this->filterInstances;
        } elseif (false === $this->options['filter'] || null === $this->options['filter']) {
            return null;
        } elseif (is_string($this->options['filter'])) {

            $filter = $this->options['filter'];
            do {
                $filter = new $filter();
                if (!($filter instanceof \Voelkel\DataTablesBundle\Table\Filter\AbstractColumnFilter)) {
                    throw new \Exception(sprintf('invalid filter class "%s"', $this->options['filter']));
                }

                $filter->setOptions($this->options['filter_options']);
                array_unshift($this->filterInstances, $filter);
                $filter = $filter->getParent();
            } while (null !== $filter);

            return $this->filterInstances;
        } elseif (is_object($this->options['filter']) && $this->options['filter'] instanceof \Voelkel\DataTablesBundle\Table\Filter\AbstractColumnFilter) {
            $this->filterInstances[] = $this->options['filter'];
            return $this->filterInstances;
        }

        throw new \Exception();
    }

    public function getFilterBlockPrefixes()
    {
        if (is_array($this->filterBlockPrefixes)) {
            return $this->filterBlockPrefixes;
        }

        if (null === ($filters = $this->getFilterInstances())) {
            throw new \Exception();
        }

        $this->filterBlockPrefixes = [];

        foreach ($filters as $filter) {
            $this->filterBlockPrefixes[] = $filter->getBlockPrefix();
        }

        return $this->filterBlockPrefixes;
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

    /**
     * @return string
     * @throws \Exception
     */
    public function getPlaceholder()
    {
        if (null === $this->options['placeholder']) {
            return $this->getLabel();
        } elseif (is_string($this->options['placeholder'])) {
            return $this->options['placeholder'];
        } elseif (false === $this->options['placeholder']) {
            return '';
        } else {
            throw new \Exception('invalid placeholder option: ' . $this->options['placeholder']);
        }
    }
}
