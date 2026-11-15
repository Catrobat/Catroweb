<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\Moderation\ContentModerationAction;
use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Enum\AppealState;
use App\DB\Enum\ReportState;
use App\Storage\StorageLifecycleService;
use App\Studio\StudioManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Content hidden by moderation is not kept as free storage: its owner has DAYS to fix or appeal it, then it is deleted.
 *
 * Due: a project, studio or comment hidden (auto_hidden_at) at least DAYS ago that a moderator decided to hide
 * (audit admin_confirmed or admin_hide_warned, at least DAYS ago, not reversed since); and everything of an account an
 * admin banned (audit admin_confirmed or admin_banned on the user) at least DAYS ago (profile_hidden_at): projects,
 * comments, and studios it is the only active admin of. The account row itself stays, so the ban holds.
 * Never due: content with a pending report, a pending or approved appeal, or an evidence hold
 * (catro:moderation:hold), content whose deletion would take a held comment along, and anything of an account with a
 * pending report, a pending appeal or a hold.
 *
 * Ops scripts that hide or ban with raw SQL should set auto_hidden_at / profile_hidden_at; rows they leave empty are
 * stamped by the next --apply run, so the DAYS start then.
 */
#[AsCommand(
  name: 'catro:moderation:purge-hidden',
  description: 'Delete content that has been hidden by moderation for 14 days (dry run unless --apply)',
)]
class PurgeHiddenContentCommand extends Command
{
  public const int DAYS = 14;

  /** content type => [table, SQL expression for the owner's id] */
  private const array TABLES = [
    'project' => ['program', 'x.user_id'],
    'studio' => ['studio', "(SELECT su.user FROM studio_user su WHERE su.studio = x.id AND su.role = 'admin' AND su.status = 'active' ORDER BY su.created_on LIMIT 1)"],
    'comment' => ['user_comment', 'x.user_id'],
  ];

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly StorageLifecycleService $storage,
    private readonly StudioManager $studio_manager,
    private readonly LoggerInterface $logger,
    private readonly ManagerRegistry $doctrine,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('apply', null, InputOption::VALUE_NONE, 'Delete; without it, only list what would be deleted')
      ->addOption('days', null, InputOption::VALUE_REQUIRED, 'Days content stays hidden before it is deleted', (string) self::DAYS)
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $apply = $input->getOption('apply');
    $days = max(1, (int) $input->getOption('days'));

    if ($apply) {
      $this->stampHiddenSince();
    }

    $cutoff = new \DateTime(sprintf('-%d days', $days))->format('Y-m-d H:i:s');
    $deleted = 0;
    $failed = 0;
    foreach (array_keys(self::TABLES) as $type) {
      foreach ($this->due($type, $cutoff) as ['id' => $id, 'owner_id' => $owner_id]) {
        $context = ['type' => $type, 'id' => $id, 'owner_id' => $owner_id];
        if (!$apply) {
          $io->writeln(sprintf('Would delete %s %s (owner %s)', $type, $id, $owner_id ?? '-'));
          ++$deleted;
          continue;
        }

        try {
          if ($this->delete($type, $id)) {
            $this->logger->info('Moderation purge: deleted {type} {id} of {owner_id}', $context);
            $io->writeln(sprintf('Deleted %s %s (owner %s)', $type, $id, $owner_id ?? '-'));
            ++$deleted;
          }
        } catch (\Throwable $e) {
          ++$failed;
          $this->logger->warning('Moderation purge: could not delete {type} {id}', [...$context, 'error' => $e->getMessage()]);
          $this->doctrine->resetManager(); // a failed flush closes the entity manager
        }

        $this->entity_manager->clear();
      }
    }

    $io->success($apply
      ? sprintf('Deleted %d items hidden by moderation for %d days (%d failed).', $deleted, $days, $failed)
      : sprintf('Dry run: %d items hidden by moderation for %d days would be deleted. Run with --apply to delete.', $deleted, $days));

    return Command::SUCCESS;
  }

  /**
   * Starts the clock for rows hidden or banned without a timestamp (raw SQL, rows from before the column existed) and
   * clears it on rows shown again the same way.
   */
  private function stampHiddenSince(): void
  {
    $connection = $this->entity_manager->getConnection();
    $now = new \DateTime()->format('Y-m-d H:i:s');
    foreach (['program' => 'auto_hidden', 'studio' => 'auto_hidden', 'user_comment' => 'auto_hidden', 'fos_user' => 'profile_hidden'] as $table => $flag) {
      // ponytail: full scans of program and user_comment once a day; index the flags if this gets slow.
      $connection->executeStatement("UPDATE {$table} SET {$flag}_at = ? WHERE {$flag} = 1 AND {$flag}_at IS NULL", [$now]);
      $connection->executeStatement("UPDATE {$table} SET {$flag}_at = NULL WHERE {$flag} = 0 AND {$flag}_at IS NOT NULL");
    }
  }

  /**
   * @return list<array{id: string, owner_id: ?string}>
   */
  private function due(string $type, string $cutoff): array
  {
    [$table, $owner] = self::TABLES[$type];
    // A banned owner takes a studio along only when no one else administers it.
    $sole_admin = 'studio' === $type
      ? "AND NOT EXISTS (SELECT 1 FROM studio_user o WHERE o.studio = x.id AND o.role = 'admin' AND o.status = 'active' AND o.user <> u.id)"
      : '';
    // Deleting a project or studio deletes its comments: a held one, or one by a held user, keeps it.
    $cascade = match ($type) {
      'project' => 'c.programId = x.id',
      'studio' => 'c.studio = x.id',
      default => null,
    };
    $cascade_held = null === $cascade ? '' : "AND NOT EXISTS (SELECT 1 FROM user_comment c WHERE {$cascade} AND (".self::held("'comment'", 'c.id').' OR '.self::held("'user'", 'c.user_id').'))';
    $restored = [ContentModerationAction::ACTION_ADMIN_REVERSED, ContentModerationAction::ACTION_APPEAL_APPROVED];
    // Only a moderator's hide counts: accept or hide_warn (a plain warn leaves visibility alone), not since restored.
    $hide_decided = self::standing(':type', 'x.id', [ContentModerationAction::ACTION_ADMIN_CONFIRMED, ContentModerationAction::ACTION_ADMIN_HIDE_WARNED], $restored, 'AND s.created_at <= :cutoff');
    // Only an admin's ban counts, not a community auto-hide of the account.
    $ban_decided = self::standing("'user'", 'u.id', [ContentModerationAction::ACTION_ADMIN_CONFIRMED, ContentModerationAction::ACTION_ADMIN_BANNED], $restored, 'AND s.created_at <= :cutoff');

    $sql = "SELECT x.id, u.id AS owner_id FROM {$table} x LEFT JOIN fos_user u ON u.id = {$owner}
      WHERE ((x.auto_hidden = 1 AND x.auto_hidden_at <= :cutoff AND {$hide_decided})
        OR (u.profile_hidden = 1 AND u.profile_hidden_at <= :cutoff AND {$ban_decided} {$sole_admin}))
      AND NOT EXISTS (SELECT 1 FROM content_report r WHERE r.content_type = :type AND r.content_id = x.id AND r.state = :new)
      AND NOT EXISTS (SELECT 1 FROM content_report r WHERE r.content_type = 'user' AND r.content_id = u.id AND r.state = :new)
      AND NOT EXISTS (SELECT 1 FROM content_appeal a WHERE a.content_type = :type AND a.content_id = x.id AND a.state IN (:pending, :approved))
      AND NOT EXISTS (SELECT 1 FROM content_appeal a WHERE a.content_type = 'user' AND a.content_id = u.id AND a.state = :pending)
      AND NOT ".self::held(':type', 'x.id').' AND NOT '.self::held("'user'", 'u.id')." {$cascade_held}
      ORDER BY x.id";

    $rows = $this->entity_manager->getConnection()->fetchAllAssociative($sql, [
      'cutoff' => $cutoff,
      'type' => $type,
      'new' => ReportState::New->value,
      'pending' => AppealState::Pending->value,
      'approved' => AppealState::Approved->value,
    ]);

    return array_map(static fn (array $row): array => [
      'id' => strval($row['id']),
      'owner_id' => null === $row['owner_id'] ? null : strval($row['owner_id']),
    ], $rows);
  }

  /** SQL condition: the content has an evidence hold that was not released since. */
  private static function held(string $type, string $id): string
  {
    return self::standing($type, $id, [ContentModerationAction::ACTION_EVIDENCE_HOLD], [ContentModerationAction::ACTION_EVIDENCE_RELEASED]);
  }

  /**
   * SQL condition: an audit row of one of $actions on the content (SQL expressions $type, $id) that no row of $undone_by
   * followed.
   *
   * @param list<string> $actions
   * @param list<string> $undone_by
   */
  private static function standing(string $type, string $id, array $actions, array $undone_by, string $and = ''): string
  {
    $in = static fn (array $values): string => "'".implode("', '", $values)."'";

    return "EXISTS (SELECT 1 FROM content_moderation_action s WHERE s.content_type = {$type} AND s.content_id = {$id} AND s.action IN ({$in($actions)}) {$and}
      AND NOT EXISTS (SELECT 1 FROM content_moderation_action l WHERE l.content_type = s.content_type AND l.content_id = s.content_id AND l.action IN ({$in($undone_by)}) AND l.id > s.id))";
  }

  /** Through the same paths an admin's deletion takes: files, search index (entity listener) and cascades. */
  private function delete(string $type, string $id): bool
  {
    $entity = $this->entity_manager->find(match ($type) {
      'project' => Project::class,
      'studio' => Studio::class,
      default => UserComment::class,
    }, $id);

    if (null === $entity) {
      return false; // already gone with its project or studio
    }

    if ($entity instanceof Project) {
      $this->storage->hardDeleteProject($entity);

      return true;
    }

    $cover = $entity instanceof Studio ? $entity->getCoverAssetPath() : null;
    $this->entity_manager->remove($entity);
    $this->entity_manager->flush();
    try {
      $this->studio_manager->deleteCoverImage($cover); // after the row: never a studio without its cover
    } catch (\Throwable $e) {
      $this->logger->warning('Moderation purge: could not delete the cover of studio {id}', ['id' => $id, 'error' => $e->getMessage()]);
    }

    return true;
  }
}
