<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Command;

use Psr\Log\LoggerInterface;
use SmartAssert\RunnerDelegator\Exception\InvalidRemotePathException;
use SmartAssert\RunnerDelegator\Exception\NonExecutableRemoteTestException;
use SmartAssert\RunnerDelegator\RunnerClient\RunnerClient;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use webignition\BasilRunnerDocuments\Exception;
use webignition\TcpCliProxyClient\Exception\ClientCreationException;
use webignition\TcpCliProxyClient\Exception\SocketErrorException;
use webignition\TcpCliProxyClient\Exception\SocketTimedOutException;
use webignition\TcpCliProxyClient\Handler;
use webignition\YamlDocumentGenerator\YamlGenerator;

#[AsCommand(name: 'run')]
class RunCommand extends Command
{
    public const string OPTION_BROWSER = 'browser';
    public const string OPTION_TIMEOUT_IN_SECONDS = 'timeout_in_seconds';
    public const string ARGUMENT_PATH = 'path';
    public const int DEFAULT_TIMEOUT_IN_SECONDS = 600;
    private const string NAME = 'run';

    /**
     * @var array<string, RunnerClient>
     */
    private array $runnerClients = [];

    /**
     * @param array<mixed> $runnerClients
     */
    public function __construct(
        array $runnerClients,
        private LoggerInterface $logger,
        private YamlGenerator $yamlGenerator
    ) {
        parent::__construct(self::NAME);

        foreach ($runnerClients as $name => $runnerClient) {
            if (is_string($name) && $runnerClient instanceof RunnerClient) {
                $this->runnerClients[$name] = $runnerClient;
            }
        }
    }

    public function __invoke(
        OutputInterface $output,
        #[Option(
            description: 'Browser to use',
            name: self::OPTION_BROWSER
        )]
        string $browser = '',
        #[Argument(
            description: 'Path to the generated test (to be passed on to a runner',
            name: self::ARGUMENT_PATH
        )]
        string $path = '',
        #[Option(name: self::OPTION_TIMEOUT_IN_SECONDS)]
        int $timeoutInSeconds = self::DEFAULT_TIMEOUT_IN_SECONDS,
    ): int {
        $runnerClient = $this->runnerClients[$browser] ?? null;

        if ($runnerClient instanceof RunnerClient) {
            try {
                $runnerClient->request($path, new Handler(), $timeoutInSeconds);
            } catch (SocketErrorException $e) {
                $this->logException($e);
            } catch (ClientCreationException $e) {
                $this->logException($e, [
                    'connection-string' => $e->getConnectionString(),
                ]);
            } catch (InvalidRemotePathException|NonExecutableRemoteTestException $remoteTestExecutionException) {
                $this->logException($remoteTestExecutionException, [
                    'remote-path' => $remoteTestExecutionException->getPath(),
                ]);

                $exception = Exception::createFromThrowable($remoteTestExecutionException)->withoutTrace();
                $output->write($this->yamlGenerator->generate($exception->getData()));
            } catch (SocketTimedOutException $socketTimedOutException) {
                $this->logException($socketTimedOutException, [
                    'timeout_in_seconds' => $timeoutInSeconds,
                ]);

                $exception = Exception::createFromThrowable($socketTimedOutException)->withoutTrace();
                $output->write($this->yamlGenerator->generate($exception->getData()));
            }
        } else {
            $this->logger->debug(
                'Unknown browser \'' . $browser . '\'',
                [
                    'browser' => $browser,
                ]
            );
        }

        return Command::SUCCESS;
    }

    /**
     * @param array<mixed> $context
     */
    private function logException(\Exception $exception, array $context = []): void
    {
        $this->logger->debug(
            $exception->getMessage(),
            $context
        );
    }
}
