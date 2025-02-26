<?php

declare(strict_types=1);

namespace Aimating\Graphql;
use TheCodingMachine\GraphQLite\SchemaFactory as BaseSchemaFactory;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Hyperf\Contract\ConfigInterface;
use GraphQL\Type\Schema;
use TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface;
use Aimating\Graphql\Factory\PaginatorTypeMapperFactory;
class SchemaFactory
{

    public function __invoke(ContainerInterface $container): Schema {
       $config = $container->get(ConfigInterface::class);
       $factory = new BaseSchemaFactory($container->get(CacheInterface::class), $container);

       $factory->addNamespace('App');
       //$factory->addNamespace('Aimating\Auth');

       if($config->get('graphql.security', true)) {
            $factory->setAuthenticationService($container->get(AuthenticationServiceInterface::class));
            $factory->setAuthorizationService($container->get(AuthorizationServiceInterface::class));
            
       }

       $factory->addTypeMapperFactory($container->get(PaginatorTypeMapperFactory::class));
     
       
       $types = $config->get('graphql.types', 'App');
       if (!is_iterable($types)) {
          $types = [ $types ];
       }
       foreach ($types as $namespace) {
            //$factory->addNamespace($namespace);
        }

        if ($config->get('APP_ENV') === 'production') {
            $factory->prodMode();
        }


       return $factory->createSchema();
    }
}
