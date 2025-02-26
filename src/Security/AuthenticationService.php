<?php

declare(strict_types=1);

namespace Aimating\Graphql\Security;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use Psr\Http\Message\ServerRequestInterface;
class AuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(protected ServerRequestInterface $request)
    {
    }

    public function getUser() : ?object
    {
        return  $this->request->getAttribute('user');
    }


    public function isLogged() : bool
    {     
        return $this->request->getAttribute('user') !== null;
    }

}
