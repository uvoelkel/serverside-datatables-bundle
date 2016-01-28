<?php

namespace Voelkel\DataTablesBundle\Tests\DataTables\Entity;

class TestUser
{
    private $id;

    private $name;

    private $status;

    private $groups;

    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function addGroup(TestGroup $group)
    {
        $group->setUser($this);

        $this->groups->add($group);
        return $this;
    }

    public function removeGroup(TestGroup $group)
    {
        $this->groups->remove($group);
        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}
