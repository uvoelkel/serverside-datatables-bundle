<?php

namespace Voelkel\DataTablesBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Voelkel\DataTablesBundle\Table\AbstractDataTable;
use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Filter\DateFilter;
use Voelkel\DataTablesBundle\Table\Filter\TextFilter;
use Voelkel\DataTablesBundle\Table\Filter\ChoiceFilter;

/**
 * @codeCoverageIgnore
 */
class ServersideDataTablesExtension extends \Twig_Extension
{
    /** @var ContainerInterface */
    private $container;

    /** @var string */
    private $theme;

    private static $defaultsRendered = false;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->theme = $container->getParameter('serverside_datatables.config')['options']['theme'];
        $container->get('twig')->addRuntimeLoader(new RuntimeLoader($container));
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('datatables_html', [$this, 'renderHtml'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new \Twig_SimpleFunction('datatables_defaults', [$this, 'renderDefaults'], [
                'needs_environment' => true,
                'is_safe' => ['html', 'js'],
            ]),
            new \Twig_SimpleFunction('datatables_js', [$this, 'renderJavascript'], [
                'needs_environment' => true,
                'is_safe' => ['html', 'js'],
            ]),
            new \Twig_SimpleFunction('datatables_id', [$this, 'getTableId'], [
                'needs_environment' => false,
                //'is_safe' => ['html'],
            ]),

            new \Twig_SimpleFunction('datatables_column_filter', [$this, 'renderColumnFilter'], [
                'needs_environment' => true,
                'needs_context' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function getTokenParsers()
    {
        return [
            new TableThemeTokenParser(),
        ];
    }

    public function renderHtml(\Twig_Environment $twig, AbstractDataTable $table, array $options = [])
    {
        $table->setContainer($this->container);

        $tableId = $table->getName();
        if (isset($options['id'])) {
            $tableId = $options['id'];
            unset($options['id']);
        }

        if (isset($options['theme'])) {
            $theme = $options['theme'];
            unset($options['theme']);
        } else {
            $theme = $this->theme;
        }

        return $twig->render('@VoelkelDataTables/table_' . $theme . '.html.twig', [
            'table' => $table,
            'options' => $options,
            'tableId' => $tableId,
        ]);
    }

    public function renderDefaults(\Twig_Environment $twig)
    {
        $result = '';
        if (false === self::$defaultsRendered) {
            $options = $this->container->get('serverside_datatables.table_options_factory')->getDefaultOptions()->all();

            $render = function(array $options, $depth = 1) use (&$render) {
                $result = '';

                $keys = array_keys($options);
                for ($i = 0; $i < sizeof($keys); $i++) {
                    $key = $keys[$i];
                    $value = $options[$key];
                    $last = ($i + 1) === sizeof($keys);

                    $result .= str_repeat("\t", $depth);
                    $result .= "'" . $key . "': ";

                    if (is_string($value)) {
                        $result .= '"' . $value . '"';
                    } elseif (is_int($value)) {
                        $result .= $value;
                    } elseif (is_bool($value)) {
                        $result .= $value ? 'true' : 'false';
                    } elseif (is_null($value)) {
                        $result .= 'null';
                    } elseif (is_array($value)) {
                        $result .= "{\n" . $render($value, $depth + 1) . "\n" . str_repeat("\t", $depth) . "}";
                    } else {
                        throw new \Exception('unhandled value ' . $value);
                    }

                    if (!$last) {
                        $result .= ",";
                    }
                    $result .= "\n";
                }

                return $result;
            };

            $result = "$.extend(true, $.fn.dataTable.defaults, {\n";
            $result .= $render($options);
            $result .= "});\n\n";

            self::$defaultsRendered = true;
        }

        return $result;
    }

    public function renderJavascript(\Twig_Environment $twig, AbstractDataTable $table, $path = null, $options = [])
    {
        $table->setContainer($this->container);

        if (null === $path) {
            $params = array_merge(
                ['table' => null !== $table->getServiceId() ? $table->getServiceId() : get_class($table)],
                ['parameters' => $table->getRequestParameters()]
            );

            $path = $this->container->get('router')->generate('serverside_datatables_list', $params);
        }

        $tableVar = $table->getName();
        if (isset($options['var'])) {
            $tableVar = $options['var'];
            unset($options['var']);
        }

        $tableId = $table->getName();
        if (isset($options['id'])) {
            $tableId = $options['id'];
            unset($options['id']);
        }

        $deferLoading = null;
        if (true === $table->getOption('deferLoading')) {
            $request = new \Symfony\Component\HttpFoundation\Request();
            $request->query->add([
                'draw' => 0,
                'start' => 0,
                'length' => 10,
            ]);

            $response = $this->container->get('serverside_datatables')->processRequest($table, $request);
            $data = json_decode($response->getContent(), true);

            $deferLoading = [
                'total' => $data['recordsTotal'],
                'filtered' => $data['recordsFiltered'],
                'rows' => [],
            ];


            foreach ($data['data'] as $row) {

                $tmp = [];

                foreach ($row as $key => $value ) {
                    if (0 === strpos($key, 'DT_')) {
                        continue;
                    }

                    $tmp[$key] = $value;
                }

                $deferLoading['rows'][] = $tmp;
            }
        }

        $result = $this->renderDefaults($twig);
        $result .= $twig->render('@VoelkelDataTables/table.js.twig', [
            'table' => $table,
            'path' => $path,
            'options' => $options,
            'tableId' => $tableId,
            'tableVar' => $tableVar,
            'deferLoading' => $deferLoading,
        ]);

        return $result;
    }

    public function getTableId(AbstractDataTable $table)
    {
        $table->setContainer($this->container);

        return $table->getName();
    }

    public function renderColumnFilter(\Twig_Environment $twig, $context, AbstractDataTable $table, $column, array $options = [])
    {
        $table->setContainer($this->container);

        $tableId = $table->getName();
        if (isset($options['id'])) {
            $tableId = $options['id'];
            unset($options['id']);
        }

        if (isset($options['theme'])) {
            $theme = $options['theme'];
            unset($options['theme']);
        } else {
            $theme = $this->theme;
        }

        if (is_string($column)) {
            $column = $table->getColumn($column);
        }

        if (!($column instanceof Column)) {
            throw new \Exception();
        }

        if (null === $column->getFilter()) {
            return '';
        }

        if (true === $column->filterRendered) {
            return '';
        }

        $column->filterRendered = true;

        if ('bootstrap4' !== $theme) {
            return $twig->render('@VoelkelDataTables/column_filter_' . $theme . '.html.twig', [
                'table' => $table,
                'column' => $column,
                'options' => $options,
                'tableId' => $tableId,
            ]);
        }

        $renderer = $twig->getRuntime(TableRenderer::class);

        $templates = [];
        $templates[] = $twig->loadTemplate('@VoelkelDataTables/filter_' . $theme . '.html.twig');
        foreach ($renderer->getThemes($table) as $theme) {
            $templates[] = $twig->loadTemplate($theme);
        }

        /** @var \Twig_Template $template */
        $template = null;
        $block = 'filter';
        foreach (array_reverse($column->getFilterBlockPrefixes()) as $prefix) {
            foreach ($templates as $tpl) {
                if ($tpl->hasBlock($prefix . '_widget', [])) {
                    $block = $prefix;
                    $template = $tpl;
                    break;
                }
            }

            if (null !== $template) {
                break;
            }
        }

        if (null === $template) {
            throw new \Exception();
        }

        return $template->renderBlock($block. '_widget', $twig->mergeGlobals([
            'table' => $table,
            'column' => $column,
            'options' => $options,
            'tableId' => $tableId,
            'id' => $tableId . '_' . $column->getName() . '_filter',
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return [
            new \Twig_SimpleTest('datatables_textfilter', function ($filter) {
                return ($filter === TextFilter::class) ||
                ($filter instanceof TextFilter) ||
                $this->is_a($filter, TextFilter::class);
            }),
            new \Twig_SimpleTest('datatables_choicefilter', function ($filter) {
                return ($filter === ChoiceFilter::class) ||
                ($filter instanceof ChoiceFilter) ||
                $this->is_a($filter, ChoiceFilter::class);
            }),
            new \Twig_SimpleTest('datatables_datefilter', function ($filter) {
                return ($filter === DateFilter::class) ||
                $this->is_a($filter, DateFilter::class);
            }),
        ];
    }

    private function is_a($object, $className)
    {
        if (null === $object) {
            return false;
        }

        if (is_a($object, $className, true)) {
            return true;
        }

        if (is_string($object) && class_exists($object)) {
            /** @var \Voelkel\DataTablesBundle\Table\Filter\AbstractColumnFilter $instance */
            $instance = new $object();
            return $this->is_a($instance->getParent(), $className);
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'serverside_datatables_extension';
    }
}
