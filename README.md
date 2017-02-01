DataTablesBundle
================
[![Build Status](https://travis-ci.org/uvoelkel/serverside-datatables-bundle.svg?branch=master)](https://travis-ci.org/uvoelkel/serverside-datatables-bundle)

## What it does

The DataTablesBundle let's you easily create (sortable and filterable) [serverSide](http://datatables.net/reference/option/serverSide) 
[DataTables](http://datatables.net/) from Doctrine entities.

## License

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE

## Installation

Enable the bundle by adding the following line in the ``app/AppKernel.php``
file of your project:

```php
# app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Voelkel\DataTablesBundle\VoelkelDataTablesBundle(),
        );

        // ...
    }

    // ...
}
```

## Configuration

After installing the bundle, make sure you add this route to your routing:

    # app/config/routing.yml
    datatables:
        resource: "@VoelkelDataTablesBundle/Resources/config/routing.xml"


### Localization

    # app/config/config.yml
    voelkel_data_tables:
        localization:
            locale: "%locale%"
            data:
                true: "Jepp"
                false: "Nope"
                datetime: "Y-m-d H:i:s"


## Usage

Create a Table definition

```php
# AppBundle/DataTable/CustomerTable.php

<?php

namespace AppBundle\DataTable;

use Voelkel\DataTablesBundle\Table\AbstractDataTable;
use Voelkel\DataTablesBundle\Table\TableOptions;
use Voelkel\DataTablesBundle\Table\TableSettings;
use Voelkel\DataTablesBundle\Table\Column\Column;
use Voelkel\DataTablesBundle\Table\Column\UnboundColumn;
use Voelkel\DataTablesBundle\Table\Column\CallbackColumn;
use Voelkel\DataTablesBundle\Table\Column\EntityColumn;
use Voelkel\DataTablesBundle\Table\Column\EntitiesColumn;    

class CustomerTable extends AbstractDataTable
{
    protected function configure(TableSettings $settings, TableOptions $options)
    {
        $settings->setName('customer');
        $settings->setEntity('AppBundle\Entity\Customer');
    }

    protected function build()
    {
        $this
            ->addColumn(new Column('id', 'id'))
            ->addColumn(new Column('gender', 'gender'))
            ->addColumn(new Column('firstname', 'firstname'))
            ->addColumn(new Column('lastname', 'lastname'))
            ->addColumn(new UnboundColumn('opening', function(Customer $customer) {
                return 'Dear ' . ('f' === $customer->getGender() ? 'Madam' : 'Sir');
            }))
            ->addColumn(new CallbackColumn('status', 'status', function($status) {
                switch ($status) {
                    case 1:
                        return 'something';
                        break;
                    case 2:
                        return 'something else';
                        break;
                    default:
                        return 'invalid';
                        break;
                }
            }))
            ->addColumn(new EntityColumn('group', 'group', 'name'))                 // customer has one group
            ->addColumn(new EntityColumn('state', 'city.state', 'name'))            // customer has one city. city has one state
            ->addColumn(new EntitiesColumn('orders', 'orders', 'number'))           // customer has many orders
            ->addColumn(new EntitiesCountColumn('addresses_count', 'addresses'))    // customer has many addresses
            ->addColumn(new ActionsColumn('actions', [
                'edit' => [
                    'title' => 'edit customer',
                    'label' => '<i class="fa fa-edit"></i>',
                    'callback' => function(Customer $customer, \Symfony\Component\Routing\RouterInterface $router) {
                        return $router->generate('customer_edit', ['id' => $customer->getId()]);
                    },
                ],
            ])
        ;
    }
}
```

In your CustomerController

```php
# AppBundle/Controller/CustomerController.php

use AppBundle\DataTable\CustomerTable;

class CustomerController extends Controller 
{
    public function indexAction()
    {
        return $this->render('AppBundle:Customer:index.html.twig', [
            'table' => new CustomerTable(),
        ]);
    }
}
```

And in your index template

    # AppBundle/Resources/views/Customer/index.html.twig

    {% extends '::base.html.twig' %}

    {% block body %}
        {{ datatables_html(table) }}
    {% endblock %}

    {% block javascripts %}
        <script>
            {{ datatables_js(table) }}

            // access the table instance
            {{ table.name }}_table.on('dblclick', 'tbody tr', function () {
                alert('dblclick');
            });
        </script>
    {% endblock %}

### Access DI container

```php
use Voelkel\DataTablesBundle\Table\AbstractDataTable;

class CustomerTable extends AbstractDataTable
{
    protected function build()
    {
        $service = $this->container->get('service');
        // or short
        $service = $this->get('service');

        // ...
        ->addColumn(new Column('id', 'id', [
            'format_data_callback' => function($data, $object, Column $column) {
                $router = $this->container->get('router');
                // or
                $router = $this->get('router');
            },
        ]))
        // ...
    }
}
```

### Table definition as a service

Define the service

    # AppBundle/Resources/config/services.xml

    <service id="app.table.customer" class="AppBundle\DataTable\CustomerTable">
        <argument type="service" id="my.awesome.service" />
    </service>

Set the service id in the table constructor

```php
# AppBundle/DataTable/CustomerTable.php

private $myAwesomeService;

public function __construct($myAwesomeService)
{
    $this->myAwesomeService = $myAwesomeService;
}

protected function configure(TableSettings $settings, TableOptions $options)
{
    $settings->setName('customer');
    $settings->setEntity('AppBundle\Entity\Customer');
    $settings->setServiceId('app.table.customer');
}
```

In your controller

```php
# AppBundle/Controller/CustomerController.php

public function indexAction()
{
    return $this->render('AppBundle:Customer:index.html.twig', [
        'table' => $this->get('app.table.customer'),
    ]);
}
```

### Column filter

```php
# AppBundle/DataTable/CustomerTable.php

// ...

class CustomerTable extends AbstractDataTable
{
    // ...
    protected function build()
    {
        $this
            // ...
            ->addColumn(new Column('gender', 'gender', [
                'filter' => 'select',
                'filter_choices' => [
                    'm' => 'male',
                    'f' => 'female',
                ],
            ]))
            ->addColumn(new Column('lastname', 'lastname', [
                'filter' => 'text',
            ]))
        ;
    }
}
```

### Table options

```php
$default = [
    'stateSave' => false,
    'stateDuration' => 7200,
];
```

- 'stateDuration': -1 sessionStorage. 0 or greater localStorage. 0 infinite. > 0 duration in seconds

```php
class CustomerTable extends AbstractTableDefinition
{
    // ...
    protected function configureOptions()
    {
        return [
            'stateSave' => true,
            'stateDuration' => 120,
        ];
    }
}
```

### Column options

```php
$default = [
    'sortable' => true,
    'searchable' => true,
    'filter' => false, // false|'text'|'select'
    'filter_choices' => [],
    'filter_empty' => false, // add a checkbox to filter empty resp. null values
    'multiple' => false,
    'expanded' => false,
    'format_data_callback' => null, // function ($data, $object, Column $column) {}
    'unbound' => false,
    'order' => null, // null|'asc'|'desc'
    'label' => null, // null|string|false
    'abbr' => null, // null|string
];
```

- 'filter' != false implies 'searchable' = true
- 'multiple' has no effect if filter != 'select'
- 'expanded' has no effect if filter != 'select'
