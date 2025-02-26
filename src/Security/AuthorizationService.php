<?php

declare(strict_types=1);

namespace Aimating\Graphql\Security;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
class AuthorizationService implements AuthorizationServiceInterface
{

    public function isAllowed(string $right, mixed $subject = null): bool
    {
       
        return true;
    }
}
