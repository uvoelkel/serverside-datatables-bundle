<?php

namespace Voelkel\DataTablesBundle\Table\Column;

use Voelkel\DataTablesBundle\Table\Filter\AbstractColumnFilter;
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

    const ORDER_ASCENDING = 'asc';
    const ORDER_DESCENDING = 'desc';

    /** @var array */
    private $options = [
        'visible' => true,
        'sortable' => true,
        'searchable' => true,
        'width' => null,
        'filter' => false, // false|null|\Voelkel\DataTablesBundle\Table\Filter\FilterInterface
        'filter_options' => [],

        /*'filter_choices' => [],
        'filter_query' => '%value%', // [%]value|split( |and)[%]
        'filter_attr' => [],
        'filter_empty' => false, // add a checkbox to filter empty resp null values
        'multiple' => false,
        'expanded' => false,*/
        
        'format_data_callback' => null, // function ($data, $entity, Column $column) {}
        'unbound' => false,
        'order' => null, // null|'asc'|'desc'
        'label' => null, // null|string|false
        'placeholder' => null, // null|string|false
        'abbr' => null,
        'responsive_priority' => null,
    ];

    static private $deprecatedOptions = [
        'filter_choices' => 'filter_options[choices]',
        'filter_query' => 'filter_options[query]',
        'filter_attr' => 'filter_options[attr]',
        'filter_empty' => 'filter_options[empty]',
        'multiple' => 'filter_options[multiple]',
        'expanded' => 'filter_options[expanded]',
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

        foreach (self::$deprecatedOptions as $deprecatedOption => $newOption) {
            if (isset($this->options[$deprecatedOption])) {
                @trigger_error(
                    'The column option "' . $deprecatedOption . '" is deprecated. Use "' . $newOption . '" instead.',
                    E_USER_DEPRECATED
                );
            }
        }

        if (false === $this->options['filter'] || null === $this->options['filter']) {
            return;
        }

        if (is_object($this->options['filter'])) {
            throw new \Exception('filter instances are not allowed.');
        }

        $this->options['searchable'] = true;


        // create filter instances
        $filter = $this->options['filter'];
        do {
            $filter = new $filter();
            if (false === ($filter instanceof \Voelkel\DataTablesBundle\Table\Filter\AbstractColumnFilter)) {
                throw new \Exception(sprintf('invalid filter class "%s"', $this->options['filter']));
            }
            array_unshift($this->filterInstances, $filter);
            $filter = $filter->getParent();
        } while (null !== $filter);

        // build options
        $filterInstance = null;
        $filterOptions = [
            'attr' => [],
            'popover' => false,
            'empty' => false,
        ];
        foreach ($this->filterInstances as $filterInstance) {
            $filterOptions = array_merge($filterOptions, $filterInstance->getDefaultOptions());
        }
        $filterOptions = array_merge($filterOptions, $this->options['filter_options']);
        $filterInstance->setOptions($filterOptions);
        $this->options['filter_options'] = $filterOptions;

        // backwards compatibility
        if (isset($this->options['filter_empty'])) {
            @trigger_error('use options[filter_options][empty]', E_USER_DEPRECATED);
            $this->options['filter_options']['empty'] = $this->options['filter_empty'];
        }

        if (TextFilter::class === $this->options['filter']) {
            if (isset($this->options['filter_query'])) {
                @trigger_error('use options[filter_options][query]', E_USER_DEPRECATED);
                $this->options['filter_options']['query'] = $this->options['filter_query'];
            }
        } elseif (ChoiceFilter::class === $this->options['filter']) {
            if (isset($this->options['filter_choices'])) {
                @trigger_error('use options[filter_options][choices]', E_USER_DEPRECATED);
                $this->options['filter_options']['choices'] = $this->options['filter_choices'];
            }

            if (isset($this->options['multiple'])) {
                @trigger_error('use options[filter_options][multiple]', E_USER_DEPRECATED);
                $this->options['filter_options']['multiple'] = $this->options['multiple'];
            }

            if (isset($this->options['expanded'])) {
                @trigger_error('use options[filter_options][expanded]', E_USER_DEPRECATED);
                $this->options['filter_options']['expanded'] = $this->options['expanded'];
            }
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
        }

        throw new \Exception();
    }

    public function getFilterBlockPrefixes()
    {
        if (false === $this->options['filter'] || null === $this->options['filter']) {
            return null;
        }

        if (null === ($filters = $this->getFilterInstances())) {
            throw new \Exception();
        }

        $result = [];
        foreach ($filters as $filter) {
            $result[] = $filter->getBlockPrefix();
        }
        return $result;
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
