<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Services;

use SmartAssert\RunnerDelegator\Exception\InvalidRemotePathException;
use SmartAssert\RunnerDelegator\Exception\NonExecutableRemoteTestException;
use Symfony\Component\Console\Output\OutputInterface;
use webignition\TcpCliProxyClient\Handler;

class RunnerClientHandlerFactory
{
    public function create(OutputInterface $output): Handler
    {
        $handler = new Handler();
        $handler = $handler
            ->addCallback(function (string $buffer, string $request) {
                if (ctype_digit($buffer)) {
                    $exitCode = (int) $buffer;

                    if (0 !== $exitCode) {
                        if (100 === $exitCode) {
                            throw new InvalidRemotePathException($request);
                        }

                        throw new NonExecutableRemoteTestException($request);
                    }
                }
            })
            ->addCallback(function (string $buffer) use ($output) {
                if (false === ctype_digit($buffer)) {
                    $output->write($buffer);
                }
            })
        ;

        return $handler;
    }
}
