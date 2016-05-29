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
            new \Twig_SimpleFunction('datatables_js', [$this, 'renderJavascript'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
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
        return 'serverside_datatables_extension';
    }
}
