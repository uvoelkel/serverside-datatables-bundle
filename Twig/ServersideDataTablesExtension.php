<?php

namespace Voelkel\DataTablesBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Voelkel\DataTablesBundle\Table\AbstractDataTable;

/**
 * @codeCoverageIgnore
 */
class ServersideDataTablesExtension extends \Twig_Extension
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @var string
     * @deprecated
     */
    private $theme = 'bootstrap3';

    private static $defaultsRendered = false;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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

        return $twig->render('@VoelkelDataTables/table_' . $this->theme . '.html.twig', [
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
            $path = $this->container->get('router')->generate('serverside_datatables_list', [
                'table' => null !== $table->getServiceId() ? $table->getServiceId() : get_class($table),
            ]);
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

        $result = $this->renderDefaults($twig);
        $result .= $twig->render('@VoelkelDataTables/table.js.twig', [
            'table' => $table,
            'path' => $path,
            'options' => $options,
            'tableId' => $tableId,
            'tableVar' => $tableVar,
        ]);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'serverside_datatables_extension';
    }
}
