<?php

namespace Voelkel\DataTablesBundle\Twig;

use Symfony\Component\Routing\RouterInterface;
use Voelkel\DataTablesBundle\Table\AbstractTableDefinition;

/**
 * @codeCoverageIgnore
 */
class DataTablesExtension extends \Twig_Extension
{
    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $theme;

    /**
     * @param RouterInterface $router
     * @param string $theme
     */
    public function __construct(RouterInterface $router, $theme)
    {
        $this->router = $router;
        $this->theme = $theme;
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
            new \Twig_SimpleFunction('datatables_js', [$this, 'renderJavascript'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function renderHtml(\Twig_Environment $twig, AbstractTableDefinition $table, array $options = [])
    {
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

    public function renderJavascript(\Twig_Environment $twig, AbstractTableDefinition $table, $path = null, $options = [])
    {
        if (null === $path) {
            $path = $this->router->generate('serverside_datatables_list', [
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

        return $twig->render('@VoelkelDataTables/table.js.twig', [
            'table' => $table,
            'path' => $path,
            'options' => $options,
            'tableId' => $tableId,
            'tableVar' => $tableVar,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'datatables_extension';
    }
}
