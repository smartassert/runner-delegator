<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Exception;

class NonExecutableRemoteTestException extends AbstractFailedTestExecutionException
{
    public function __construct(string $path)
    {
        parent::__construct($path, sprintf('Failed to execute test "%s"', $path));
    }
}
