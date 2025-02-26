<?php

declare(strict_types=1);

namespace Aimating\Graphql\Factory;
use GraphQL\Server\ServerConfig;
use Psr\Container\ContainerInterface;
use GraphQL\Type\Schema;
use TheCodingMachine\GraphQLite\Context\Context;
use TheCodingMachine\GraphQLite\Exceptions\WebonyxErrorHandler;
use Hyperf\Contract\ConfigInterface;
class ServerConfigFactory
{

    public function __invoke(ContainerInterface $container)
    {
        $serverConfig = new ServerConfig();
        $config = $container->get(ConfigInterface::class);
        $serverConfig->setSchema($container->get(Schema::class));
        $serverConfig->setErrorFormatter([WebonyxErrorHandler::class, 'errorFormatter']);
        $serverConfig->setErrorsHandler([WebonyxErrorHandler::class, 'errorHandler']);
        $serverConfig->setContext(new Context());
        return $serverConfig;
    }
}
