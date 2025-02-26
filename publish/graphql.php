<?php

declare(strict_types=1);

use GraphQL\Error\DebugFlag;
use function Hyperf\Support\env;

return [

    'debug' => DebugFlag::RETHROW_UNSAFE_EXCEPTIONS|DebugFlag::INCLUDE_TRACE,
    'uri' => env('GRAPHQLITE_URI', '/graphql'),
    'enable' => true,
    'servers' => [
       'http' => [
        'middlewares' =>  [
             
        ]
       ],
    ],
    
];