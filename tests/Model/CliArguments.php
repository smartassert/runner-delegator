<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Tests\Model;

class CliArguments
{
    public function __construct(
        private string $browser,
        private string $target,
    ) {}

    public function __toString(): string
    {
        return sprintf('--browser %s %s', $this->browser, $this->target);
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function getTarget(): string
    {
        return $this->target;
    }
}
