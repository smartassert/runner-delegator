<?php

declare(strict_types=1);

namespace webignition\BasilRunnerDelegator\Tests\Integration;

use Symfony\Component\Process\Process;
use webignition\BasilRunnerDelegator\Tests\Model\CliArguments;
use webignition\BasilRunnerDelegator\Tests\Model\DelegatorCliCommand;
use webignition\BasilRunnerDelegator\Tests\Model\ExecutionOutput;

class LocalDelegatorTest extends AbstractDelegatorTest
{
    protected function getExecutionOutput(CliArguments $cliArguments): ExecutionOutput
    {
        $process = Process::fromShellCommandline((string) new DelegatorCliCommand($cliArguments));
        $exitCode = $process->run();

        return new ExecutionOutput($process->getOutput(), $exitCode);
    }
}
