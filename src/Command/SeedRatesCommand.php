<?php

namespace App\Command;

use App\Service\TaxYearSeeder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed-rates', description: 'Create the default UK tax year rates when none exist.')]
class SeedRatesCommand extends Command
{
    public function __construct(private readonly TaxYearSeeder $seeder)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->seeder->seed()) {
            $io->success('Default UK tax year 2026/27 rates created and activated.');
        } else {
            $io->note('Tax year rates already exist; nothing was created.');
        }

        return Command::SUCCESS;
    }
}
