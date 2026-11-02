<?php

namespace App\Command;

use App\Entity\AdminUser;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Create the administrator account or reset its PIN.')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly AdminUserRepository $adminUsers,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('pin', InputArgument::REQUIRED, 'A 4 to 8 digit administrator PIN.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $pin = (string) $input->getArgument('pin');

        if (!preg_match('/^\d{4,8}$/', $pin)) {
            $io->error('The PIN must be between 4 and 8 digits.');

            return Command::FAILURE;
        }

        $admin = $this->adminUsers->findAdmin() ?? (new AdminUser())->setUsername('admin');
        $admin->setPinHash($this->passwordHasher->hashPassword($admin, $pin));

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success('Administrator account is ready. Sign in with the configured PIN.');

        return Command::SUCCESS;
    }
}
