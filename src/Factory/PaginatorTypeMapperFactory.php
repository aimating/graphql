<?php

declare(strict_types=1);

namespace Aimating\Graphql\Factory;
use TheCodingMachine\GraphQLite\FactoryContext;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperFactoryInterface;
use TheCodingMachine\GraphQLite\Mappers\TypeMapperInterface;
use  Aimating\Graphql\Mappers\PaginatorTypeMapper;
class PaginatorTypeMapperFactory implements TypeMapperFactoryInterface
{

    public function create(FactoryContext $context): TypeMapperInterface
    {echo 555;
        return new PaginatorTypeMapper($context->getRecursiveTypeMapper());
    }
}
