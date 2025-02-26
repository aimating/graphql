<?php

declare(strict_types=1);

namespace Aimating\Graphql\Factory;
use Psr\Container\ContainerInterface;
class AuthorizationServiceFactory
{

    public function __invoke(ContainerInterface $container)
    {
        return new \Aimating\Graphql\Security\AuthorizationService();
    }
}
