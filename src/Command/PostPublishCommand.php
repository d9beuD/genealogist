<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:post-publish',
    description: 'Add a short description for your command',
)]
class PostPublishCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {}

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $makeDiffCommand = $this->getApplication()->find('doctrine:migrations:diff');
        $makeDiffInput = new ArrayInput(['--allow-empty-diff' => true]);
        $applyDiffCommand = $this->getApplication()->find('doctrine:migrations:migrate');
        $applyDiffInput = new ArrayInput([]);
        $clearCacheCommand = $this->getApplication()->find('cache:clear');

        $makeDiffInput->setInteractive(false);
        $applyDiffInput->setInteractive(false);

        $io->info('Looking for database changes');
        try {
            $makeDiffCommand->run($makeDiffInput, $output);

            $io->info('Diff found, applying them');
            $applyDiffCommand->run($applyDiffInput, $output);
        } catch (Exception $e) {
            $io->info('No diff found');
        }

        $io->info('Clearing application cache');
        $clearCacheCommand->run($input, $output);

        return Command::SUCCESS;
    }
}
