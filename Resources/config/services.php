<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Voelkel\DataTablesBundle\Controller\ServerSideController;
use Voelkel\DataTablesBundle\DataTables\DataToStringConverter;
use Voelkel\DataTablesBundle\DataTables\ServerSide;
use Voelkel\DataTablesBundle\DataTables\TableOptionsFactory;
use Voelkel\DataTablesBundle\Twig\ServersideDataTablesExtension;

return App::config([
    'services' => [
        '_defaults' => [
            'autowire' => false,
            'autoconfigure' => false,
        ],
        ServerSideController::class => [
            'class' => ServerSideController::class,
            'public' => true,
            'arguments' => [
                service('service_container'),
                service('security.token_storage')->nullOnInvalid(),
                service('security.authorization_checker')->nullOnInvalid(),
            ],
            'tags' => ['controller.service_arguments'],
        ],
        'serverside_datatables.twig_extension' => [
            'class' => ServersideDataTablesExtension::class,
            'public' => false,
            'arguments' => [
                service('service_container'),
                service('twig')
            ],
            'tags' => ['twig.extension'],
        ],
        'serverside_datatables.table_options_factory' => [
            'class' => TableOptionsFactory::class,
            'public' => true,
            'arguments' => [
                param('serverside_datatables.config'),
                param('kernel.default_locale'),
            ]
        ],
        'serverside_datatables.data_to_string_converter' => [
            'class' => DataToStringConverter::class,
            'public' => false,
            'arguments' => [
                 param('kernel.default_locale'),
            ]
        ],
        ServerSide::class => [
            'class' => ServerSide::class,
            'public' => true,
            'arguments' => [
                service('doctrine.orm.entity_manager'),
                service('serverside_datatables.data_to_string_converter'),
            ]
        ]   ,
        'serverside_datatables' => [
            'alias' => ServerSide::class,
        ],
    ]
]);
