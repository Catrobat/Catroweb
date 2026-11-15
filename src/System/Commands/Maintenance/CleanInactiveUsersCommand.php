<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\User\User;
use App\System\Mail\MailerAdapter;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * An account nobody logged in to for two years that has nothing in it (no projects, comments or studio
 * memberships) is mailed a warning, and deleted 30 days later unless someone logged in since.
 * Staff (any stored role) and Scratch placeholders are never touched.
 */
#[AsCommand(
  name: 'catrobat:clean:inactive-users',
  description: 'Warn and later delete accounts without login, projects or comments for two years',
)]
class CleanInactiveUsersCommand extends Command
{
  public const string INACTIVE_FOR = '-2 years';

  public const int DAYS_UNTIL_DELETION = 30;

  /** Warnings share the free daily mail budget with verification mails: spread them over days. */
  public const int WARNINGS_PER_RUN = 100;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly UserManager $user_manager,
    private readonly MailerAdapter $mailer,
    private readonly TranslatorInterface $translator,
    private readonly LoggerInterface $cleanupLogger,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only count what would be warned, deleted or kept')
      ->addOption('max-warnings', null, InputOption::VALUE_REQUIRED, 'Warning mails per run', (string) self::WARNINGS_PER_RUN)
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $format = static fn (string $modify): string => new \DateTime($modify)->format('Y-m-d H:i:s');

    // A warned account is judged by what happened since its warning; an unwarned one by the last two years.
    $kept = $this->userIds('u.inactivity_warned_at IS NOT NULL AND NOT '.self::idleSince('u.inactivity_warned_at'));
    $due = $this->userIds('u.inactivity_warned_at < :due AND '.self::idleSince('u.inactivity_warned_at'), ['due' => $format(sprintf('-%d days', self::DAYS_UNTIL_DELETION))]);
    $to_warn = $this->userIds('u.inactivity_warned_at IS NULL AND '.self::idleSince(':cutoff'), ['cutoff' => $format(self::INACTIVE_FOR)]);

    if ($input->getOption('dry-run')) {
      $io->success(sprintf('Dry run: %d accounts would be deleted, %d are waiting for a warning, %d kept because someone logged in or added something.', count($due), count($to_warn), count($kept)));

      return Command::SUCCESS;
    }

    foreach ($this->users($kept) as $user) {
      $user->setInactivityWarnedAt(null);
    }

    $this->entity_manager->flush();

    $deleted = 0;
    foreach (array_chunk($due, 50) as $chunk) {
      foreach ($this->users($chunk) as $user) {
        $this->user_manager->delete($user, false);
        ++$deleted;
      }

      $this->entity_manager->flush();
      $this->entity_manager->clear();
    }

    $warned = 0;
    foreach ($this->users(array_slice($to_warn, 0, max(0, (int) $input->getOption('max-warnings')))) as $user) {
      if (!$this->warn($user)) {
        break; // the daily mail budget is used up: the rest wait for the next run
      }

      $user->setInactivityWarnedAt(new \DateTime());
      $this->entity_manager->flush();
      ++$warned;
    }

    $this->cleanupLogger->info('cleanup_run', ['rule' => 'inactive_users', 'deleted' => $deleted, 'warned' => $warned, 'kept' => count($kept), 'waiting' => count($to_warn) - $warned]);
    $io->success(sprintf('Deleted %d inactive accounts, warned %d (%d still waiting), kept %d.', $deleted, $warned, count($to_warn) - $warned, count($kept)));

    return Command::SUCCESS;
  }

  private function warn(User $user): bool
  {
    $email = (string) $user->getEmail();

    return '' !== $email && $this->mailer->send(
      $email,
      $this->translator->trans('inactiveAccount.subject', [], 'catroweb'),
      'Security/InactiveAccount/InactiveAccountEmail.html.twig',
      ['username' => $user->getUserIdentifier(), 'days' => self::DAYS_UNTIL_DELETION],
      'management',
    );
  }

  /**
   * SQL condition on fos_user u: an ordinary account with nothing in it, and no login since $since (an SQL expression).
   */
  private static function idleSince(string $since): string
  {
    return "(u.scratch_user_id IS NULL
      AND u.roles NOT LIKE '%ROLE%'
      AND COALESCE(u.last_login, u.created_at) < {$since}
      AND NOT EXISTS (SELECT 1 FROM program p WHERE p.user_id = u.id)
      AND NOT EXISTS (SELECT 1 FROM user_comment c WHERE c.user_id = u.id)
      AND NOT EXISTS (SELECT 1 FROM studio_user s WHERE s.user = u.id))";
  }

  /**
   * @param array<string, string> $params
   *
   * @return list<string>
   */
  private function userIds(string $where, array $params = []): array
  {
    return array_map(strval(...), $this->entity_manager->getConnection()->fetchFirstColumn('SELECT u.id FROM fos_user u WHERE '.$where.' ORDER BY u.id', $params));
  }

  /**
   * @param list<string> $ids
   *
   * @return list<User>
   */
  private function users(array $ids): array
  {
    return [] === $ids ? [] : $this->entity_manager->getRepository(User::class)->findBy(['id' => $ids]);
  }
}
