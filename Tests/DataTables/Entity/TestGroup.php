<?php

namespace Voelkel\DataTablesBundle\Tests\DataTables\Entity;

class TestGroup
{
    private $id;

    private $user;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param TestUser $user
     * @return $this
     */
    public function setUser(TestUser $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return TestUser|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
