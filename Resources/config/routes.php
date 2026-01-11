<?php

namespace Symfony\Component\Routing\Loader\Configurator;

use Voelkel\DataTablesBundle\Controller\ServerSideController;

return Routes::config([
    'serverside_datatables_list' => [
        'path' => '/datatables/list/{table}',
        'methods' => ['GET'],
        'controller' => [ServerSideController::class, 'list'],
        'requirements' => ['table' => '[A-z0-9_\-.]*'],
    ]
]);
