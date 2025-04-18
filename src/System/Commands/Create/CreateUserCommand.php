<?php

declare(strict_types=1);

namespace App\System\Commands\Create;

use App\User\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:user:create', description: 'Create a user')]
final class CreateUserCommand extends Command
{
  public function __construct(private readonly UserManager $userManager)
  {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->setDefinition([
        new InputArgument('username', InputArgument::REQUIRED, 'The username'),
        new InputArgument('email', InputArgument::REQUIRED, 'The email'),
        new InputArgument('password', InputArgument::REQUIRED, 'The password'),
        new InputOption('super-admin', null, InputOption::VALUE_NONE, 'Set the user as super admin'),
        new InputOption('inactive', null, InputOption::VALUE_NONE, 'Set the user as inactive'),
      ])
      ->setHelp(
        <<<'EOT'
                    The <info>%command.full_name%</info> command creates a user:

                      <info>php %command.full_name% matthieu matthieu@example.com mypassword</info>

                    You can create a super admin via the super-admin flag:

                      <info>php %command.full_name% admin admi@example.com mypassword --super-admin</info>

                    You can create an inactive user (will not be able to log in):

                      <info>php %command.full_name% user user@example.com mypassword --inactive</info>

                    EOT
      )
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $username = $input->getArgument('username');
    $email = $input->getArgument('email');
    $password = $input->getArgument('password');
    $inactive = $input->getOption('inactive');
    $superAdmin = $input->getOption('super-admin');

    $user = $this->userManager->create();
    $user->setUsername($username);
    $user->setEmail($email);
    $user->setPlainPassword($password);
    $user->setEnabled(!$inactive);
    $user->setSuperAdmin($superAdmin);
    $user->setVerified(true);

    $this->userManager->save($user);

    $output->writeln(sprintf('Created user "%s".', $username));

    return 0;
  }
}
