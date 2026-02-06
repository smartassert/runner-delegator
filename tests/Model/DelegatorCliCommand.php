<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Tests\Model;

class DelegatorCliCommand
{
    public function __construct(
        private CliArguments $cliArguments,
    ) {}

    public function __toString(): string
    {
        return './bin/delegator ' . $this->cliArguments;
    }
}
