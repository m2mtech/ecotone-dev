<?php

declare(strict_types=1);

namespace Ecotone\Messaging\Config;

use Ecotone\Messaging\Channel\ChannelInterceptorBuilder;
use Ecotone\Messaging\Channel\MessageChannelBuilder;
use Ecotone\Messaging\Config\Container\CompilableBuilder;
use Ecotone\Messaging\Config\Container\Compiler\CompilerPass;
use Ecotone\Messaging\Config\Container\Reference;
use Ecotone\Messaging\Endpoint\ChannelAdapterConsumerBuilder;
use Ecotone\Messaging\Endpoint\MessageHandlerConsumerBuilder;
use Ecotone\Messaging\Endpoint\PollingMetadata;
use Ecotone\Messaging\Handler\Gateway\GatewayProxyBuilder;
use Ecotone\Messaging\Handler\MessageHandlerBuilder;
use Ecotone\Messaging\Handler\Processor\MethodInvoker\AroundInterceptorBuilder;
use Ecotone\Messaging\Handler\Processor\MethodInvoker\MethodInterceptor;
use Ecotone\Messaging\Handler\Type;
use Psr\Container\ContainerInterface;

/**
 * Class Configuration
 * @package Ecotone\Messaging\Config
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
interface Configuration extends CompilerPass
{
    /**
     * @param MessageChannelBuilder $messageChannelBuilder
     * @return Configuration
     */
    public function registerMessageChannel(MessageChannelBuilder $messageChannelBuilder): Configuration;

    /**
     * @param MessageChannelBuilder $messageChannelBuilder
     *
     * @return Configuration
     */
    public function registerDefaultChannelFor(MessageChannelBuilder $messageChannelBuilder): Configuration;

    /**
     * @param MessageHandlerBuilder $messageHandlerBuilder
     * @return Configuration
     */
    public function registerMessageHandler(MessageHandlerBuilder $messageHandlerBuilder): Configuration;

    /**
     * @param PollingMetadata $pollingMetadata
     * @return Configuration
     */
    public function registerPollingMetadata(PollingMetadata $pollingMetadata): Configuration;

    /**
     * @param ChannelInterceptorBuilder $channelInterceptorBuilder
     * @return Configuration
     */
    public function registerChannelInterceptor(ChannelInterceptorBuilder $channelInterceptorBuilder): Configuration;

    /**
     * @param string[]|string $asynchronousChannelNames
     * @param string $targetEndpointId
     * @return Configuration
     */
    public function registerAsynchronousEndpoint(array|string $asynchronousChannelNames, string $targetEndpointId): Configuration;

    /**
     * @param MethodInterceptor $methodInterceptor
     * @return Configuration
     */
    public function registerBeforeSendInterceptor(MethodInterceptor $methodInterceptor): Configuration;

    /**
     * @param MethodInterceptor $methodInterceptor
     * @return Configuration
     */
    public function registerBeforeMethodInterceptor(MethodInterceptor $methodInterceptor): Configuration;

    /**
     * @param \Ecotone\Messaging\Handler\Processor\MethodInvoker\AroundInterceptorBuilder $aroundInterceptorReference
     * @return Configuration
     */
    public function registerAroundMethodInterceptor(AroundInterceptorBuilder $aroundInterceptorReference): Configuration;

    /**
     * @param MethodInterceptor $methodInterceptor
     * @return Configuration
     */
    public function registerAfterMethodInterceptor(MethodInterceptor $methodInterceptor): Configuration;

    public function requireConsumer(string $endpointId): Configuration;

    /**
     * @param ChannelAdapterConsumerBuilder $consumerBuilder
     *
     * @return Configuration
     */
    public function registerConsumer(ChannelAdapterConsumerBuilder $consumerBuilder): Configuration;

    /**
     * @param MessageHandlerConsumerBuilder $consumerFactory
     * @return Configuration
     */
    public function registerConsumerFactory(MessageHandlerConsumerBuilder $consumerFactory): Configuration;

    /**
     * @param GatewayProxyBuilder $gatewayBuilder
     * @return Configuration
     */
    public function registerGatewayBuilder(GatewayProxyBuilder $gatewayBuilder): Configuration;

    /**
     * @return GatewayProxyBuilder[]
     */
    public function getRegisteredGateways(): array;

    /**
     * @return ConsoleCommandConfiguration[]
     */
    public function getRegisteredConsoleCommands(): array;

    public function registerConsoleCommand(ConsoleCommandConfiguration $consoleCommandConfiguration): Configuration;

    /**
     * @param Type $interfaceName
     * @return Configuration
     */
    public function registerInternalGateway(Type $interfaceName): Configuration;

    /**
     * @param CompilableBuilder $converterBuilder
     * @return Configuration
     */
    public function registerConverter(CompilableBuilder $converterBuilder): Configuration;

    /**
     * @param string $referenceName
     * @return Configuration
     */
    public function registerMessageConverter(string $referenceName): Configuration;

    public function buildMessagingSystemFromConfiguration(?ContainerInterface $externalReferenceSearchService = null): ConfiguredMessagingSystem;

    public function registerServiceDefinition(string|Reference $id, Container\Definition $definition): Configuration;
}
