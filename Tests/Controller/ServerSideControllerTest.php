<?php

namespace Voelkel\DataTablesBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Voelkel\DataTablesBundle\Controller\ServerSideController;

class ServerSideControllerTest extends KernelTestCase
{
    public function testListAction()
    {
        self::bootKernel();

        $controller = new ServerSideController();
        $controller->setContainer(self::$kernel->getContainer());

        /*
        $table = 'Voelkel\DataTablesBundle\Tests\Controller\TestTableDefinition';
        $request = Request::create('/datatables/list/' . urlencode($table), Request::METHOD_GET, [
            'draw' => 1,
            'columns' => [],
            'start' => 0,
            'length' => 25,
            'search' => [
                'value' => '',
                'regex' => 'false',
            ],
            '_' => time(),
        ]);

        $response = $controller->listAction($table, $request);
        */


        $table = 'Non\Existing\Class';
        $request = Request::create('/datatables/list/' . urlencode($table));
        // BackendBundle%5CDataTable%5CArticleTable');
        // ?draw=2&columns%5B0%5D%5Bdata%5D=manufacturer&columns%5B0%5D%5Bname%5D=manufacturer&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=true&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=name&columns%5B1%5D%5Bname%5D=name&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=manufacturer_categories&columns%5B2%5D%5Bname%5D=manufacturer_categories&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=categories&columns%5B3%5D%5Bname%5D=categories&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=1&order%5B0%5D%5Bdir%5D=asc&start=0&length=25&search%5Bvalue%5D=&search%5Bregex%5D=false&_=1446965381607

        $this->setExpectedException('\Exception', 'table definition class "' . $table . '" not found.');
        $controller->listAction($table, $request);






        self::ensureKernelShutdown();
    }
}
