<?php

namespace Voelkel\DataTablesBundle\Twig;

use Symfony\Component\Routing\RouterInterface;
use Voelkel\DataTablesBundle\Table\AbstractTableDefinition;

/**
 * @codeCoverageIgnore
 */
class DataTablesExtension extends \Twig_Extension
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
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
        if (!isset($options['class'])) {
            $options['class'] = 'table table-striped table-bordered table-hover table-condensed table-responsive';
        }

        return $twig->render('@VoelkelDataTables/table.html.twig', [
            'table' => $table,
            'options' => $options,
        ]);
    }

    public function renderJavascript(\Twig_Environment $twig, AbstractTableDefinition $table, $path = null)
    {
        if (null === $path) {
            $path = $this->router->generate('voelkel_datatables_list', [
                'table' => get_class($table),
            ]);
        }

        return $twig->render('@VoelkelDataTables/table.js.twig', [
            'table' => $table,
            'path' => $path,
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
