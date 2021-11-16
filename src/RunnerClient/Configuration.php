<?php

declare(strict_types=1);

namespace webignition\BasilRunnerDelegator\RunnerClient;

class Configuration
{
    public const KEY_HOST = 'host';
    public const KEY_PORT = 'port';

    public function __construct(
        private string $host,
        private int $port
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $host = $data[self::KEY_HOST] ?? '';
        $host = is_string($host) ? $host : '';

        $port = $data[self::KEY_PORT] ?? 0;
        $port = is_int($port) || (ctype_digit($port) && is_string($port)) ? (int) $port : 0;

        return new Configuration($host, $port);
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }
}
