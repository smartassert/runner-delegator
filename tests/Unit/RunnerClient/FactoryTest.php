<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Tests\Unit\RunnerClient;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartAssert\RunnerDelegator\RunnerClient\ConfigurationFactory;
use SmartAssert\RunnerDelegator\RunnerClient\Factory;
use SmartAssert\RunnerDelegator\RunnerClient\RunnerClient;
use webignition\TcpCliProxyClient\Handler;
use webignition\TcpCliProxyClient\Services\ConnectionStringFactory;

class FactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @param array<mixed>   $env
     * @param RunnerClient[] $expectedClients
     */
    #[DataProvider('loadFromEnvDataProvider')]
    public function testLoadFromEnv(array $env, Handler $handler, array $expectedClients): void
    {
        $factory = new Factory(
            new ConfigurationFactory(),
            new ConnectionStringFactory(),
            $handler
        );

        $clients = $factory->loadFromEnv($env);

        self::assertEquals($expectedClients, $clients);
    }

    /**
     * @return array<mixed>
     */
    public static function loadFromEnvDataProvider(): array
    {
        $connectionStringFactory = new ConnectionStringFactory();
        $handler = \Mockery::mock(Handler::class);

        return [
            'single client, host then port' => [
                'env' => [
                    'CHROME_RUNNER_HOST' => 'chrome-runner',
                    'CHROME_RUNNER_PORT' => '9000',
                ],
                'handler' => $handler,
                'expectedClients' => [
                    'chrome' => (new RunnerClient(
                        $connectionStringFactory->createFromHostAndPort('chrome-runner', 9000),
                        $handler
                    )),
                ],
            ],
            'two clients' => [
                'env' => [
                    'CHROME_RUNNER_HOST' => 'chrome-runner',
                    'CHROME_RUNNER_PORT' => '9000',
                    'FIREFOX_RUNNER_HOST' => 'firefox-runner',
                    'FIREFOX_RUNNER_PORT' => '9001',
                ],
                'handler' => $handler,
                'expectedClients' => [
                    'chrome' => (new RunnerClient(
                        $connectionStringFactory->createFromHostAndPort('chrome-runner', 9000),
                        $handler
                    )),
                    'firefox' => (new RunnerClient(
                        $connectionStringFactory->createFromHostAndPort('firefox-runner', 9001),
                        $handler
                    )),
                ],
            ],
        ];
    }
}
