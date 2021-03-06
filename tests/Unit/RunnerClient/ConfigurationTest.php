<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Tests\Unit\RunnerClient;

use PHPUnit\Framework\TestCase;
use SmartAssert\RunnerDelegator\RunnerClient\Configuration;

class ConfigurationTest extends TestCase
{
    public function testCreate(): void
    {
        $host = 'chrome-runner';
        $port = 9000;

        $configuration = new Configuration($host, $port);

        self::assertSame($host, $configuration->getHost());
        self::assertSame($port, $configuration->getPort());
    }

    /**
     * @dataProvider fromArrayDataProvider
     *
     * @param array<mixed> $data
     */
    public function testCreateFromArray(array $data, Configuration $expectedConfiguration): void
    {
        self::assertEquals($expectedConfiguration, Configuration::fromArray($data));
    }

    /**
     * @return array<mixed>
     */
    public function fromArrayDataProvider(): array
    {
        return [
            'empty' => [
                'data' => [],
                'expectedConfiguration' => new Configuration('', 0),
            ],
            'host not present' => [
                'data' => [
                    Configuration::KEY_PORT => 123,
                ],
                'expectedConfiguration' => new Configuration('', 123),
            ],
            'port not present' => [
                'data' => [
                    Configuration::KEY_HOST => 'hostname',
                ],
                'expectedConfiguration' => new Configuration('hostname', 0),
            ],
            'host not string' => [
                'data' => [
                    Configuration::KEY_HOST => 456,
                    Configuration::KEY_PORT => 123,
                ],
                'expectedConfiguration' => new Configuration('', 123),
            ],
            'port not numeric' => [
                'data' => [
                    Configuration::KEY_HOST => 'hostname',
                    Configuration::KEY_PORT => 'port',
                ],
                'expectedConfiguration' => new Configuration('hostname', 0),
            ],
            'valid' => [
                'data' => [
                    Configuration::KEY_HOST => 'hostname',
                    Configuration::KEY_PORT => 123,
                ],
                'expectedConfiguration' => new Configuration('hostname', 123),
            ],
        ];
    }
}
