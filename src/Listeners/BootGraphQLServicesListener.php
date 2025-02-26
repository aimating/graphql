<?php

declare(strict_types=1);

namespace Aimating\Graphql\Listeners;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Contract\ContainerInterface;;
use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Aimating\Graphql\GraphQLProvider;
use GraphQL\Error\DebugFlag;
use GraphQL\Server\StandardServer;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TheCodingMachine\GraphQLite\Http\HttpCodeDeciderInterface;
use Psr\Http\Message\ServerRequestInterface;
class BootGraphQLServicesListener implements ListenerInterface
{

    public function __construct(protected ContainerInterface $container,protected ConfigInterface $config)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
   
        $config = $this->container->get(ConfigInterface::class);
      
        if (! $config->get('graphql.enable', false)) {
            return;
        }

        $path = $config->get('graphql.uri', '/graphql');
        $servers = $config->get('graphql.servers');
  
        if(empty($servers)) return;
        
        $factory = $this->container->get(DispatcherFactory::class);
          
        $handler = new GraphQLProvider(
            $this->container->get(StandardServer::class),
            $this->container->get(HttpCodeDeciderInterface::class),
            $this->container->get(ResponseFactoryInterface::class),
              $config->get('graphql.debug', DebugFlag::RETHROW_UNSAFE_EXCEPTIONS|DebugFlag::INCLUDE_TRACE),
               );
        foreach ($servers as $server => $serverConfig) {
            $router = $factory->getRouter($server);
            
             $router->addRoute(['GET', 'POST'], $path,    fn(ServerRequestInterface $request) => $handler->process($request)
             ,[
                'middlewares' => $serverConfig['middlewares'] ?? [],
             ]);
       }
        
      
    }
}
