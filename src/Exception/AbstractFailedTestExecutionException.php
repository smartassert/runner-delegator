<?php

declare(strict_types=1);

namespace webignition\BasilRunnerDelegator\Exception;

abstract class AbstractFailedTestExecutionException extends \Exception
{
    public function __construct(
        private string $path,
        string $message,
        int $code = 0
    ) {
        parent::__construct($message, $code);
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
