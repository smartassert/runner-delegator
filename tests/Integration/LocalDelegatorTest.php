<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Tests\Integration;

use SmartAssert\RunnerDelegator\Tests\Model\CliArguments;
use SmartAssert\RunnerDelegator\Tests\Model\DelegatorCliCommand;
use SmartAssert\RunnerDelegator\Tests\Model\ExecutionOutput;
use Symfony\Component\Process\Process;

class LocalDelegatorTest extends AbstractDelegatorTestCase
{
    protected function getExecutionOutput(CliArguments $cliArguments): ExecutionOutput
    {
        $process = Process::fromShellCommandline((string) new DelegatorCliCommand($cliArguments));
        $exitCode = $process->run();

        return new ExecutionOutput($process->getOutput(), $exitCode);
    }
}
