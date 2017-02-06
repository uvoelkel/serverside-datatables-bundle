<?php

namespace Voelkel\DataTablesBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ScopeInterface;
use Voelkel\DataTablesBundle\Controller\ServerSideController;
use Voelkel\DataTablesBundle\DataTables\TableOptionsFactory;
use Voelkel\DataTablesBundle\Table\AbstractTableDefinition;

class TestServerSide
{
    public function processRequest(AbstractTableDefinition $table, \Symfony\Component\HttpFoundation\Request $request)
    {
        return true;
    }
}

class TestContainer implements ContainerInterface
{
    private $services = [];

    public function __construct(\Doctrine\ORM\EntityManagerInterface $em = null)
    {
        if (null === $em) {
            $this->services['serverside_datatables'] = new TestServerSide();
        } else {
            $this->services['serverside_datatables'] = new \Voelkel\DataTablesBundle\DataTables\ServerSide(
                $em, new \Voelkel\DataTablesBundle\DataTables\DataToStringConverter('en')
            );
        }


        $this->services['serverside_datatables.table_options_factory'] = new TableOptionsFactory([], 'en');
    }

    public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->services[$id];
    }

    public function set($id, $service, $scope = null) // = self::SCOPE_CONTAINER)
    {
        $this->services[$id] = $service;
    }

    public function has($id)
    {
        return isset($this->services[$id]);
    }

    public function getParameter($name)
    {
        if ('serverside_datatables.config' === $name) {
            return [
                'localization' => [
                    'locale' => 'en',
                ],
            ];
        }

        return null;
    }

    public function hasParameter($name) {}
    public function setParameter($name, $value) {}
    public function enterScope($name) {}
    public function leaveScope($name) {}
    public function addScope(ScopeInterface $scope) {}
    public function hasScope($name) {}
    public function isScopeActive($name) {}
    public function initialized($id) { return true; }
}

class ServerSideControllerTest extends \PHPUnit_Framework_TestCase //KernelTestCase
{
    public function testListActionByClassName()
    {
        $controller = new ServerSideController();
        $controller->setContainer(new TestContainer());

        $table = 'Voelkel\DataTablesBundle\Tests\DataTables\TestTable';
        $request = Request::create('/datatables/list/' . urlencode($table));

        // ?draw=2&columns%5B0%5D%5Bdata%5D=manufacturer&columns%5B0%5D%5Bname%5D=manufacturer&columns%5B0%5D%5Bsearchable%5D=true&columns%5B0%5D%5Borderable%5D=true&columns%5B0%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B0%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B1%5D%5Bdata%5D=name&columns%5B1%5D%5Bname%5D=name&columns%5B1%5D%5Bsearchable%5D=true&columns%5B1%5D%5Borderable%5D=true&columns%5B1%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B1%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B2%5D%5Bdata%5D=manufacturer_categories&columns%5B2%5D%5Bname%5D=manufacturer_categories&columns%5B2%5D%5Bsearchable%5D=true&columns%5B2%5D%5Borderable%5D=true&columns%5B2%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B2%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B3%5D%5Bdata%5D=categories&columns%5B3%5D%5Bname%5D=categories&columns%5B3%5D%5Bsearchable%5D=true&columns%5B3%5D%5Borderable%5D=true&columns%5B3%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B3%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=1&order%5B0%5D%5Bdir%5D=asc&start=0&length=25&search%5Bvalue%5D=&search%5Bregex%5D=false&_=1446965381607


        $result = $controller->listAction($table, $request);
        $this->assertTrue($result);
    }

    public function testListActionByServiceId()
    {
        $container = new TestContainer();
        $container->set('datatables.table.test', new \Voelkel\DataTablesBundle\Tests\DataTables\TestTable());

        $controller = new ServerSideController();
        $controller->setContainer($container);

        $table = 'datatables.table.test';
        $request = Request::create('/datatables/list/' . urlencode($table));

        $result = $controller->listAction($table, $request);
        $this->assertTrue($result);
    }

    public function testListActionWithUnknownTableClass()
    {
        //self::bootKernel();

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $controller = new ServerSideController();
        $controller->setContainer(new TestContainer($em));
        //$controller->setContainer(self::$kernel->getContainer());

        $table = 'Non\Existing\TableDefinition';
        $request = Request::create('/datatables/list/' . urlencode($table));

        $this->setExpectedException('\Exception', 'table definition class or service "' . $table . '" not found.');
        $controller->listAction($table, $request);
        //self::ensureKernelShutdown();
    }
}
