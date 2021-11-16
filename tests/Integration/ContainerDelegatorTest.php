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
        $delegatorClientOutput = '';
        $delegatorClient = Client::createFromHostAndPort('localhost', 9003);

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
