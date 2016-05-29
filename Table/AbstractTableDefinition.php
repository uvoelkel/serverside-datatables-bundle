<?php

namespace Voelkel\DataTablesBundle\Table;

/**
 * @deprecated
 */
abstract class AbstractTableDefinition extends AbstractDataTable
{
    /**
     * @param string $entity
     * @param string $name
     * @param string|null $serviceId
     */
    public function __construct($entity = null, $name = null, $serviceId = null, $triggerDeprecation = true)
    {
        if ($triggerDeprecation) {
            @trigger_error(
                'The '.__CLASS__.' class is deprecated. Use the Voelkel\DataTablesBundle\AbstractDataTable class instead.',
                E_USER_DEPRECATED
            );
        }

        $this->entity = $entity;
        $this->name = $name;
        $this->serviceId = $serviceId;
    }
}
