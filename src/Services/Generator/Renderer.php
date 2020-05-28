<?php

declare(strict_types=1);

namespace webignition\BasilRunner\Services\Generator;

use Symfony\Component\Console\Output\OutputInterface;
use webignition\BasilRunner\Model\GenerateCommand\OutputInterface as GenerateCommandOutputInterface;

class Renderer
{
    private OutputInterface $output;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function render(GenerateCommandOutputInterface $generateCommandOutput): void
    {
        if ($this->output instanceof OutputInterface) {
            $this->output->writeln((string) json_encode($generateCommandOutput, JSON_PRETTY_PRINT));
        }
    }
}
