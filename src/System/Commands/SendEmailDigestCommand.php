<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\User\Notification\EmailDigestService;
use App\User\Notification\EmailNotificationPreference;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'catrobat:email:send-digest',
  description: 'Send email notifications to users (immediate, daily digest, or weekly digest)',
)]
class SendEmailDigestCommand extends Command
{
  public function __construct(
    private readonly EmailDigestService $digestService,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption(
        'period',
        'p',
        InputOption::VALUE_REQUIRED,
        'Notification period: "immediate", "daily", or "weekly"',
        'immediate'
      )
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $period = $input->getOption('period');

    $preference = match ($period) {
      'immediate' => EmailNotificationPreference::IMMEDIATE,
      'daily' => EmailNotificationPreference::DAILY,
      'weekly' => EmailNotificationPreference::WEEKLY,
      default => null,
    };

    if (null === $preference) {
      $io->error(sprintf('Invalid period "%s". Use "immediate", "daily", or "weekly".', $period));

      return Command::FAILURE;
    }

    $io->info(sprintf('Sending %s notification emails...', $period));

    $sent = $this->digestService->sendDigests($preference);

    $io->success(sprintf('Sent %d %s notification email(s).', $sent, $period));

    return Command::SUCCESS;
  }
}
