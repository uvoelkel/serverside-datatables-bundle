<?php

namespace Voelkel\DataTablesBundle\Table;

/**
 * @deprecated
 */
abstract class AbstractContainerAwareTableDefinition extends AbstractTableDefinition
{
    public function __construct($entity = null, $name = null, $serviceId = null)
    {
        @trigger_error(
            'The '.__CLASS__.' class is deprecated. Use the Voelkel\DataTablesBundle\AbstractDataTable class instead.',
            E_USER_DEPRECATED
        );

        parent::__construct($entity, $name, $serviceId, false);
    }
}
