<?php

declare(strict_types=1);

namespace Aimating\Graphql;
use GraphQL\Type\Schema;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQLite\Http\HttpCodeDecider;
use TheCodingMachine\GraphQLite\Http\HttpCodeDeciderInterface;
class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Schema::class                          => SchemaFactory::class,
                ServerConfig::class                    => Factory\ServerConfigFactory::class,
                StandardServer::class                  => Factory\StandardServerFactory::class,
                AuthenticationServiceInterface::class  => Factory\AuthenticationServiceFactory::class,
                AuthorizationServiceInterface::class   => Factory\AuthorizationServiceFactory::class,
                HttpCodeDeciderInterface::class        => HttpCodeDecider::class,
            ],
            'listeners' => [
                 Listeners\BootGraphQLServicesListener::class
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for graphql.',
                    'source' => __DIR__ . '/../publish/graphql.php',
                    'destination' => BASE_PATH . '/config/autoload/graphql.php',
                ],
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
