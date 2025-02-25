<?php

namespace Ecotone\Messaging\Config\Container\Compiler;

use Ecotone\Messaging\Config\ConfiguredMessagingSystem;
use Ecotone\Messaging\Config\Container\ChannelResolverWithContainer;
use Ecotone\Messaging\Config\Container\ContainerBuilder;
use Ecotone\Messaging\Config\Container\Definition;
use Ecotone\Messaging\Config\Container\Reference;
use Ecotone\Messaging\Config\Container\ReferenceSearchServiceWithContainer;
use Ecotone\Messaging\Config\MessagingSystemContainer;
use Ecotone\Messaging\Config\ServiceCacheConfiguration;
use Ecotone\Messaging\ConfigurationVariableService;
use Ecotone\Messaging\Handler\Bridge\Bridge;
use Ecotone\Messaging\Handler\ChannelResolver;
use Ecotone\Messaging\Handler\Enricher\PropertyEditorAccessor;
use Ecotone\Messaging\Handler\Enricher\PropertyReaderAccessor;
use Ecotone\Messaging\Handler\ExpressionEvaluationService;
use Ecotone\Messaging\Handler\ReferenceSearchService;
use Ecotone\Messaging\Handler\SymfonyExpressionEvaluationAdapter;
use Ecotone\Messaging\InMemoryConfigurationVariableService;
use Ecotone\Messaging\NullableMessageChannel;
use Ecotone\Messaging\Scheduling\Clock;
use Ecotone\Messaging\Scheduling\EpochBasedClock;
use Psr\Container\ContainerInterface;

class RegisterSingletonMessagingServices implements CompilerPass
{
    public function process(ContainerBuilder $builder): void
    {
        $this->registerDefault($builder, Bridge::class, new Definition(Bridge::class));
        $this->registerDefault($builder, Reference::toChannel(NullableMessageChannel::CHANNEL_NAME), new Definition(NullableMessageChannel::class));
        $this->registerDefault($builder, Clock::class, new Definition(EpochBasedClock::class));
        $this->registerDefault($builder, ChannelResolver::class, new Definition(ChannelResolverWithContainer::class, [new Reference(ContainerInterface::class)]));
        $this->registerDefault($builder, ReferenceSearchService::class, new Definition(ReferenceSearchServiceWithContainer::class, [new Reference(ContainerInterface::class)]));
        $this->registerDefault($builder, ExpressionEvaluationService::REFERENCE, new Definition(SymfonyExpressionEvaluationAdapter::class, [new Reference(ReferenceSearchService::class)], 'create'));
        $this->registerDefault($builder, ServiceCacheConfiguration::class, new Definition(ServiceCacheConfiguration::class, factory: 'noCache'));
        $this->registerDefault($builder, PropertyEditorAccessor::class, new Definition(PropertyEditorAccessor::class, [new Reference(ExpressionEvaluationService::REFERENCE)], 'create'));
        $this->registerDefault($builder, PropertyReaderAccessor::class, new Definition(PropertyReaderAccessor::class));
        $this->registerDefault($builder, ConfiguredMessagingSystem::class, new Definition(MessagingSystemContainer::class, [new Reference(ContainerInterface::class), [], []]));
        $this->registerDefault($builder, ConfigurationVariableService::class, new Definition(InMemoryConfigurationVariableService::class, [], 'createEmpty'));
    }

    private function registerDefault(ContainerBuilder $builder, string $id, object|array|string $definition): void
    {
        if (! $builder->has($id)) {
            $builder->register($id, $definition);
        }
    }
}
