<?php

namespace Voelkel\DataTablesBundle\Table;

class TableSettings implements \ArrayAccess
{
    private $entity;

    private $name;

    private $serviceId;

    /**
     * @param string $entity
     * @return TableSettings
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $name
     * @return TableSettings
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $serviceId
     * @return TableSettings
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return in_array($offset, ['entity', 'name', 'service']);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'entity':
                return $this->entity;
                break;
            case 'name':
                return $this->name;
                break;
            case 'service':
                return $this->serviceId;
                break;
            default:
                return null;
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        switch ($offset) {
            case 'entity':
                $this->entity = $value;
                break;
            case 'name':
                $this->name = $value;
                break;
            case 'service':
                $this->serviceId = $value;
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        switch ($offset) {
            case 'entity':
                $this->entity = null;
                break;
            case 'name':
                $this->name = null;
                break;
            case 'service':
                $this->serviceId = null;
                break;
        }
    }
}
