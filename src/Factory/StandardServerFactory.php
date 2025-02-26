<?php

declare(strict_types=1);

namespace Aimating\Graphql\Factory;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use Psr\Container\ContainerInterface;
class StandardServerFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $serverConfig = $container->get(ServerConfig::class);
        return new StandardServer($serverConfig);
    }
}
