<?php

declare(strict_types=1);

namespace webignition\BasilRunnerDelegator\Tests\Integration;

use webignition\BasilRunnerDelegator\Tests\Model\CliArguments;
use webignition\BasilRunnerDelegator\Tests\Model\DelegatorCliCommand;
use webignition\BasilRunnerDelegator\Tests\Model\ExecutionOutput;
use webignition\TcpCliProxyClient\Client;
use webignition\TcpCliProxyClient\Handler;

class ContainerDelegatorTest extends AbstractDelegatorTest
{
    protected function getExecutionOutput(CliArguments $cliArguments): ExecutionOutput
    {
        $delegatorPort = $_ENV['DELEGATOR_PORT'] ?? null;
        if (!is_string($delegatorPort)) {
            throw new \RuntimeException('Delegator port not configured. Please set in phpunit configuration.');
        }


        $delegatorClientOutput = '';
        $delegatorClient = Client::createFromHostAndPort('localhost', (int) $delegatorPort);

        $delegatorClientHandler = (new Handler())
            ->addCallback(function (string $buffer) use (&$delegatorClientOutput) {
                $delegatorClientOutput .= $buffer;
            })
        ;

        $delegatorClient->request(
            (string) new DelegatorCliCommand($cliArguments),
            $delegatorClientHandler
        );

        $delegatorClientOutputLines = explode("\n", $delegatorClientOutput);
        $delegatorExitCode = (int) array_pop($delegatorClientOutputLines);
        $delegatorClientOutputContent = implode("\n", $delegatorClientOutputLines);

        return new ExecutionOutput($delegatorClientOutputContent, $delegatorExitCode);
    }
}
