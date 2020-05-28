<?php

declare(strict_types=1);

namespace webignition\BasilRunner\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use webignition\BasilRunner\Services\ProjectRootPathProvider;
use webignition\BasilRunner\Services\ResultPrinter\ResultPrinter;
use webignition\SymfonyConsole\TypedInput\TypedInput;

class RunCommand extends Command
{
    public const OPTION_PATH = 'path';

    public const RETURN_CODE_INVALID_PATH = 100;
    public const RETURN_CODE_UNABLE_TO_OPEN_PROCESS = 200;

    private const NAME = 'basil-runner:run';
    private const DEFAULT_RELATIVE_PATH = '/generated';

    private string $projectRootPath;

    public function __construct(ProjectRootPathProvider $projectRootPathProvider)
    {
        $this->projectRootPath = $projectRootPathProvider->get();

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Command description')
            ->addOption(
                self::OPTION_PATH,
                null,
                InputOption::VALUE_REQUIRED,
                'Absolute path to the directory of tests to run.',
                $this->projectRootPath . self::DEFAULT_RELATIVE_PATH
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $typedInput = new TypedInput($input);

        $path = trim((string) $typedInput->getStringOption(RunCommand::OPTION_PATH));
        if (!is_dir($path)) {
            return self::RETURN_CODE_INVALID_PATH;
        }

        $process = popen($this->createPhpUnitCommand($path), 'r');

        if (is_resource($process)) {
            while ($buffer = fgets($process)) {
                $output->write($buffer);
            }

            return pclose($process);
        }

        return self::RETURN_CODE_UNABLE_TO_OPEN_PROCESS;
    }

    private function createPhpUnitCommand(string $path): string
    {
        $phpUnitExecutablePath = $this->projectRootPath . '/vendor/bin/phpunit';
        $phpUnitConfigurationPath = $this->projectRootPath . '/phpunit.run.xml';

        return $phpUnitExecutablePath .
            ' -c ' . $phpUnitConfigurationPath .
            ' --colors=always ' .
            ' --printer="' . ResultPrinter::class . '" ' .
            $path;
    }
}
