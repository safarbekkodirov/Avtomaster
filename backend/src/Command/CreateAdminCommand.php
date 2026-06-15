<?php
// src/Command/CreateAdminCommand.php

namespace App\Command;

use App\Domain\User\Entity\User;
use App\Domain\User\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name:        'app:create-admin',
    description: 'Создать администратора системы',
)]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface      $em,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email    = $io->ask('Email администратора');
        $password = $io->askHidden('Пароль (скрыт)');
        $firstName = $io->ask('Имя');
        $lastName  = $io->ask('Фамилия');

        $user = new User(email: $email, roles: ['ROLE_ADMIN']);
        $user->setPasswordHash($this->hasher->hashPassword($user, $password));

        $profile = new UserProfile(
            user:      $user,
            firstName: $firstName,
            lastName:  $lastName,
        );

        $this->em->persist($user);
        $this->em->persist($profile);
        $this->em->flush();

        $io->success(sprintf('Администратор %s создан (ID: %d)', $email, $user->getId()));

        return Command::SUCCESS;
    }
}
