<?php

declare(strict_types=1);

namespace SmartAssert\RunnerDelegator\Command;

use Psr\Log\LoggerInterface;
use SmartAssert\RunnerDelegator\Exception\InvalidRemotePathException;
use SmartAssert\RunnerDelegator\Exception\NonExecutableRemoteTestException;
use SmartAssert\RunnerDelegator\RunnerClient\RunnerClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use webignition\BasilRunnerDocuments\Exception;
use webignition\TcpCliProxyClient\Exception\ClientCreationException;
use webignition\TcpCliProxyClient\Exception\SocketErrorException;
use webignition\YamlDocumentGenerator\YamlGenerator;

class RunCommand extends Command
{
    public const OPTION_BROWSER = 'browser';
    public const ARGUMENT_PATH = 'path';

    private const NAME = 'run';

    /**
     * @var array<string, RunnerClient>
     */
    private array $runnerClients;

    /**
     * @param RunnerClient[] $runnerClients
     */
    public function __construct(
        array $runnerClients,
        private LoggerInterface $logger,
        private YamlGenerator $yamlGenerator
    ) {
        parent::__construct(self::NAME);

        $this->runnerClients = array_filter($runnerClients, function ($item) {
            return $item instanceof RunnerClient;
        });
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Command description')
            ->addOption(
                self::OPTION_BROWSER,
                null,
                InputOption::VALUE_REQUIRED,
                'Browser to use'
            )
            ->addArgument(
                self::ARGUMENT_PATH,
                InputArgument::REQUIRED,
                'Path to the generated test (to be passed on to a runner)'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $browser = $input->getOption(self::OPTION_BROWSER);
        $browser = is_string($browser) ? $browser : '';

        $path = $input->getArgument(self::ARGUMENT_PATH);
        $path = is_string($path) ? $path : '';

        $runnerClient = $this->runnerClients[$browser] ?? null;

        if ($runnerClient instanceof RunnerClient) {
            try {
                $runnerClient->request($path);
            } catch (SocketErrorException $e) {
                $this->logException($e);
            } catch (ClientCreationException $e) {
                $this->logException($e, [
                    'connection-string' => $e->getConnectionString(),
                ]);
            } catch (InvalidRemotePathException | NonExecutableRemoteTestException $remoteTestExecutionException) {
                $this->logException($remoteTestExecutionException, [
                    'remote-path' => $remoteTestExecutionException->getPath(),
                ]);

                $exception = Exception::createFromThrowable($remoteTestExecutionException)->withoutTrace();
                $output->write($this->yamlGenerator->generate($exception));
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
