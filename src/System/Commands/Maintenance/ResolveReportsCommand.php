<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\Moderation\ContentReportRepository;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportState;
use App\Moderation\ReportProcessor;
use App\User\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Resolves every pending report on one piece of content through ReportProcessor, exactly like the admin
 * moderation queue: reporters are notified, the audit row is written and trust caches are invalidated.
 */
#[AsCommand(
  name: 'catro:moderation:resolve',
  description: 'Resolve all pending reports on a piece of content (accept|reject|hide_warn|warn)',
)]
class ResolveReportsCommand extends Command
{
  public function __construct(
    private readonly ContentReportRepository $report_repository,
    private readonly ReportProcessor $report_processor,
    private readonly UserManager $user_manager,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addArgument('content_type', InputArgument::REQUIRED, 'project, user, studio or comment')
      ->addArgument('content_id', InputArgument::REQUIRED, 'Id of the reported content')
      ->addArgument('action', InputArgument::REQUIRED, implode('|', ReportProcessor::ACTIONS))
      ->addOption('admin', null, InputOption::VALUE_REQUIRED, 'Username of the admin the resolution is recorded for')
      ->addOption('message', null, InputOption::VALUE_REQUIRED, 'Warning text for the owner (hide_warn/warn); default text if omitted')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);

    $content_type = ContentType::tryFrom($input->getArgument('content_type'));
    $content_id = $input->getArgument('content_id');
    $action = $input->getArgument('action');
    if (null === $content_type || !in_array($action, ReportProcessor::ACTIONS, true)) {
      $io->error('Invalid content type or action.');

      return Command::INVALID;
    }

    $admin = $this->user_manager->findUserByUsername((string) $input->getOption('admin'));
    if (!$admin instanceof User || !$admin->hasRole('ROLE_ADMIN') && !$admin->hasRole('ROLE_SUPER_ADMIN')) {
      $io->error('--admin must name an existing admin user.');

      return Command::INVALID;
    }

    $pending = array_values(array_filter(
      $this->report_repository->findReportsForContent($content_type->value, $content_id),
      static fn ($report): bool => ReportState::New->value === $report->getState(),
    ));
    if ([] === $pending) {
      $io->warning('No pending reports for this content.');

      return Command::SUCCESS;
    }

    // Resolving one report resolves every pending report on the same content.
    $message = $input->getOption('message');
    $this->report_processor->resolveReport($pending[0], $admin, $action, is_string($message) ? $message : null);
    $io->success(sprintf('%d report(s) resolved with "%s".', count($pending), $action));

    return Command::SUCCESS;
  }
}
