<?php

declare(strict_types=1);

namespace Ecotone\Dbal\ObjectManager;

use Doctrine\Persistence\ManagerRegistry;
use Ecotone\Messaging\Config\Container\Definition;
use Ecotone\Messaging\Config\Container\MessagingContainerBuilder;
use Ecotone\Messaging\Config\Container\Reference;
use Ecotone\Modelling\RepositoryBuilder;
use Enqueue\Dbal\ManagerRegistryConnectionFactory;
use ReflectionClass;

class DoctrineORMRepositoryBuilder implements RepositoryBuilder
{
    public function __construct(private string $connectionReferenceName, private ?array $relatedClasses)
    {
    }

    public function canHandle(string $aggregateClassName): bool
    {
        if (is_null($this->relatedClasses)) {
            return true;
        }

        return in_array($aggregateClassName, $this->relatedClasses);
    }

    public function isEventSourced(): bool
    {
        return false;
    }

    public function compile(MessagingContainerBuilder $builder): Definition
    {
        return new Definition(ManagerRegistryRepository::class, [
            new Reference($this->connectionReferenceName),
            $this->relatedClasses,
        ], [self::class, 'createFromManagerRegistryConnectionFactory']);
    }

    public static function createFromManagerRegistryConnectionFactory(ManagerRegistryConnectionFactory $connectionFactory, ?array $relatedClasses)
    {
        // TODO: this seems really wrong to use reflection here
        $registry = new ReflectionClass($connectionFactory);
        $property = $registry->getProperty('registry');
        $property->setAccessible(true);
        /** @var ManagerRegistry $registry */
        $registry = $property->getValue($connectionFactory);

        return new ManagerRegistryRepository($registry, $relatedClasses);
    }
}
