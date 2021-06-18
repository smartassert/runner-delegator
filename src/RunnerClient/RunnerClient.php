<?php

declare(strict_types=1);

namespace webignition\BasilRunnerDelegator\RunnerClient;

use webignition\BasilRunnerDelegator\Exception\InvalidRemotePathException;
use webignition\BasilRunnerDelegator\Exception\NonExecutableRemoteTestException;
use webignition\TcpCliProxyClient\Client;
use webignition\TcpCliProxyClient\Exception\ClientCreationException;
use webignition\TcpCliProxyClient\Exception\SocketErrorException;
use webignition\TcpCliProxyClient\Handler;

class RunnerClient extends Client
{
    private const RUNNER_COMMAND = './bin/runner --path=%s';

    public function __construct(
        string $connectionString,
        private Handler $handler
    ) {
        parent::__construct($connectionString);
    }

    /**
     * @throws ClientCreationException
     * @throws SocketErrorException
     * @throws InvalidRemotePathException
     * @throws NonExecutableRemoteTestException
     */
    public function request(string $request, Handler $handler = null): void
    {
        parent::request(
            sprintf(self::RUNNER_COMMAND, $request),
            $this->handler
        );
    }
}
