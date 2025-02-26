<?php

declare(strict_types=1);

namespace Aimating\Graphql\Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
class AuthenticationServiceFactory
{

    public function __invoke(ContainerInterface $container)
    {
        return new \Aimating\Graphql\Security\AuthenticationService($container->get(ServerRequestInterface::class));  
    }
}
