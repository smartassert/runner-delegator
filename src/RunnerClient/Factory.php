<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\RunnerClient;

use webignition\TcpCliProxyClient\Handler;
use webignition\TcpCliProxyClient\Services\ConnectionStringFactory;

class Factory
{
    public function __construct(
        private ConfigurationFactory $runnerClientConfigurationFactory,
        private ConnectionStringFactory $connectionStringFactory,
        private Handler $handler
    ) {
    }

    /**
     * @param array<mixed> $env
     *
     * @return RunnerClient[]
     */
    public function loadFromEnv(array $env): array
    {
        $clients = [];
        $configurations = $this->runnerClientConfigurationFactory->createCollectionFromEnv($env);

        foreach ($configurations as $name => $configuration) {
            $clients[$name] = new RunnerClient(
                $this->connectionStringFactory->createFromHostAndPort(
                    $configuration->getHost(),
                    $configuration->getPort()
                ),
                $this->handler
            );
        }

        return $clients;
    }
}
