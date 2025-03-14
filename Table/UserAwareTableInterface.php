<?php

namespace Voelkel\DataTablesBundle\Table;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserAwareTableInterface
{
    public function setUser(?UserInterface $user): static;

    public function setAuthorizationChecker(?AuthorizationCheckerInterface $authorizationChecker): static;
}
