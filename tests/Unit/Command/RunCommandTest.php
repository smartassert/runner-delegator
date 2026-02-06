<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Tests\Unit\Command;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SmartAssert\RunnerDelegator\Command\RunCommand;
use SmartAssert\RunnerDelegator\Exception\InvalidRemotePathException;
use SmartAssert\RunnerDelegator\Exception\NonExecutableRemoteTestException;
use SmartAssert\RunnerDelegator\RunnerClient\RunnerClient;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use webignition\BasilRunnerDocuments\Exception;
use webignition\TcpCliProxyClient\Exception\ClientCreationException;
use webignition\TcpCliProxyClient\Exception\SocketErrorException;
use webignition\YamlDocumentGenerator\YamlGenerator;

class RunCommandTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @param RunnerClient[] $runnerClients
     */
    #[DataProvider('runSuccessDataProvider')]
    public function testRunSuccess(
        array $runnerClients,
        string $browser,
        string $path,
        OutputInterface $commandOutput,
        ?LoggerInterface $logger = null
    ): void {
        $input = new ArrayInput([
            '--browser' => $browser,
            'path' => $path,
        ]);

        $logger = $logger ?? \Mockery::mock(LoggerInterface::class);

        $command = new RunCommand(
            $runnerClients,
            $logger,
            new YamlGenerator()
        );
        $exitCode = $command->run($input, $commandOutput);

        self::assertSame(0, $exitCode);
    }

    /**
     * @return array<mixed>
     */
    public static function runSuccessDataProvider(): array
    {
        $testPath = '/target/GeneratedChromeTest.php';

        $chromeInvalidRemotePathException = new InvalidRemotePathException($testPath);
        $chromeNonExecutableTestException = new NonExecutableRemoteTestException($testPath);

        $yamlGenerator = new YamlGenerator();

        return [
            'has runner clients, test for unknown browser' => [
                'runnerClients' => [
                    'chrome' => self::createRunnerClient($testPath),
                ],
                'browser' => 'unknown',
                'path' => $testPath,
                'commandOutput' => \Mockery::mock(OutputInterface::class),
                'logger' => self::createLogger(
                    'Unknown browser \'unknown\'',
                    [
                        'browser' => 'unknown',
                    ]
                ),
            ],
            'client request throws SocketErrorException' => [
                'runnerClients' => [
                    'chrome' => self::createRunnerClient(
                        $testPath,
                        new SocketErrorException(
                            new \ErrorException('socket error exception message')
                        )
                    ),
                ],
                'browser' => 'chrome',
                'path' => $testPath,
                'commandOutput' => \Mockery::mock(OutputInterface::class),
                'logger' => self::createLogger('socket error exception message', []),
            ],
            'client request throws ClientCreationException' => [
                'runnerClients' => [
                    'chrome' => self::createRunnerClient(
                        $testPath,
                        new ClientCreationException('connection string', 'client creation exception message', 123)
                    ),
                ],
                'browser' => 'chrome',
                'path' => $testPath,
                'commandOutput' => \Mockery::mock(OutputInterface::class),
                'logger' => self::createLogger('client creation exception message', [
                    'connection-string' => 'connection string',
                ]),
            ],
            'has runner clients, throws InvalidRemotePathException' => [
                'runnerClients' => [
                    'chrome' => self::createRunnerClient($testPath, $chromeInvalidRemotePathException),
                ],
                'browser' => 'chrome',
                'path' => $testPath,
                'commandOutput' => self::createCommandOutput([
                    'write' => [
                        $yamlGenerator->generate(
                            Exception::createFromThrowable($chromeInvalidRemotePathException)
                                ->withoutTrace()
                                ->getData()
                        ),
                    ],
                ]),
                'logger' => self::createLogger(
                    'Path "/target/GeneratedChromeTest.php" not present on runner',
                    [
                        'remote-path' => '/target/GeneratedChromeTest.php',
                    ]
                ),
            ],
            'has runner clients, throws NonExecutableRemoteTestException' => [
                'runnerClients' => [
                    'chrome' => self::createRunnerClient($testPath, $chromeNonExecutableTestException),
                ],
                'browser' => 'chrome',
                'path' => $testPath,
                'commandOutput' => self::createCommandOutput([
                    'write' => [
                        $yamlGenerator->generate(
                            Exception::createFromThrowable($chromeNonExecutableTestException)
                                ->withoutTrace()
                                ->getData()
                        ),
                    ],
                ]),
                'logger' => self::createLogger(
                    'Failed to execute test "/target/GeneratedChromeTest.php"',
                    [
                        'remote-path' => '/target/GeneratedChromeTest.php',
                    ]
                ),
            ],
        ];
    }

    private static function createRunnerClient(string $expectedTarget, ?\Exception $throwable = null): RunnerClient
    {
        $client = \Mockery::mock(RunnerClient::class);

        if ($throwable instanceof \Throwable) {
            $client
                ->shouldReceive('request')
                ->with($expectedTarget)
                ->andThrow($throwable)
            ;
        } else {
            $client
                ->shouldReceive('request')
                ->with($expectedTarget)
            ;
        }

        return $client;
    }

    /**
     * @param array<mixed> $debugContext
     */
    private static function createLogger(string $debugExceptionMessage, array $debugContext): LoggerInterface
    {
        $logger = \Mockery::mock(LoggerInterface::class);
        $logger
            ->shouldReceive('debug')
            ->with($debugExceptionMessage, $debugContext)
        ;

        return $logger;
    }

    /**
     * @param array<string, string[]> $calls
     */
    private static function createCommandOutput(array $calls): OutputInterface
    {
        $output = \Mockery::mock(OutputInterface::class);

        foreach ($calls as $methodName => $argumentCollection) {
            foreach ($argumentCollection as $argument) {
                $output
                    ->shouldReceive($methodName)
                    ->with($argument)
                ;
            }
        }

        return $output;
    }
}
