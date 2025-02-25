<?php

namespace Ecotone\Messaging\Endpoint\PollingConsumer;

use Ecotone\Messaging\Endpoint\ConsumerLifecycle;
use Ecotone\Messaging\Endpoint\EndpointRunner;
use Ecotone\Messaging\Endpoint\ExecutionPollingMetadata;
use Ecotone\Messaging\Endpoint\PollingMetadata;
use Ecotone\Messaging\Handler\NonProxyGateway;
use Ecotone\Messaging\MessagePoller;
use Ecotone\Messaging\Scheduling\Clock;
use Ecotone\Messaging\Scheduling\CronTrigger;
use Ecotone\Messaging\Scheduling\PeriodicTrigger;
use Ecotone\Messaging\Scheduling\SyncTaskScheduler;
use Psr\Log\LoggerInterface;

class InterceptedConsumerRunner implements EndpointRunner
{
    public function __construct(
        private NonProxyGateway $gateway,
        private MessagePoller $messagePoller,
        private PollingMetadata $defaultPollingMetadata,
        private Clock $clock,
        private LoggerInterface $logger
    ) {
    }

    public function runEndpointWithExecutionPollingMetadata(?ExecutionPollingMetadata $executionPollingMetadata = null): void
    {
        $this->createConsumer($executionPollingMetadata)->run();
    }

    public function createConsumer(?ExecutionPollingMetadata $executionPollingMetadata): ConsumerLifecycle
    {
        $pollingMetadata = $this->defaultPollingMetadata->applyExecutionPollingMetadata($executionPollingMetadata);
        $interceptors = InterceptedConsumer::createInterceptorsForPollingMetadata($pollingMetadata, $this->logger);
        $interceptedGateway = new InterceptedGateway($this->gateway, $interceptors);

        $interceptedConsumer = new ScheduledTaskConsumer(
            SyncTaskScheduler::createWithEmptyTriggerContext($this->clock, $pollingMetadata),
            $pollingMetadata->getCron()
                ? CronTrigger::createWith($pollingMetadata->getCron())
                : PeriodicTrigger::create($pollingMetadata->getFixedRateInMilliseconds(), $pollingMetadata->getInitialDelayInMilliseconds()),
            new PollToGatewayTaskExecutor($this->messagePoller, $interceptedGateway),
        );

        if ($interceptors) {
            return new InterceptedConsumer($interceptedConsumer, $interceptors);
        } else {
            return $interceptedConsumer;
        }
    }
}
