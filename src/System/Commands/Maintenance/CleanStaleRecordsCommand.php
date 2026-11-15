<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\RefreshToken;
use App\DB\Entity\User\Comment\UserComment;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Rows nothing can use any more: expired refresh tokens, and comments their author deleted more than 30 days ago.
 * Bulk deletes instead of gesdinet:jwt:clear, which loads every expired token into the entity manager first.
 */
#[AsCommand(
  name: 'catrobat:clean:stale-records',
  description: 'Delete expired refresh tokens and soft-deleted comments older than 30 days',
)]
class CleanStaleRecordsCommand extends Command
{
  public const string COMMENT_RETENTION = '-30 days';

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly LoggerInterface $cleanupLogger,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only count what would be deleted');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = $input->getOption('dry-run');

    $tokens = $this->entity_manager->createQueryBuilder()
      ->from(RefreshToken::class, 't')
      ->where('t.valid < :now')
      ->setParameter('now', new \DateTime())
    ;
    $comment_ids = $this->purgeableCommentIds();

    if ($dry_run) {
      $token_count = (int) $tokens->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();
      $io->success(sprintf('Dry run: %d expired refresh tokens and %d deleted comments would be removed.', $token_count, count($comment_ids)));

      return Command::SUCCESS;
    }

    $token_count = (int) $tokens->delete()->getQuery()->execute();
    // Notifications about the comment are set to null and its translations cascade, both by foreign key.
    $comment_count = [] === $comment_ids ? 0 : (int) $this->entity_manager->createQueryBuilder()
      ->delete(UserComment::class, 'c')
      ->where('c.id IN (:ids)')
      ->setParameter('ids', $comment_ids)
      ->getQuery()
      ->execute()
    ;

    $this->cleanupLogger->info('cleanup_run', ['rule' => 'expired_refresh_tokens', 'deleted' => $token_count]);
    $this->cleanupLogger->info('cleanup_run', ['rule' => 'deleted_comments', 'deleted' => $comment_count]);
    $io->success("Deleted {$token_count} expired refresh tokens and {$comment_count} deleted comments.");

    return Command::SUCCESS;
  }

  /**
   * Soft-deleted comments older than the retention, unless a reply that is still visible hangs below them:
   * the "deleted" placeholder keeps that thread readable. There is no deleted-at column, so age is the post date.
   *
   * @return array<string>
   */
  private function purgeableCommentIds(): array
  {
    return array_map(strval(...), $this->entity_manager->createQueryBuilder()
      ->select('c.id')
      ->from(UserComment::class, 'c')
      ->where('c.is_deleted = true')
      ->andWhere('c.uploadDate < :cutoff')
      ->andWhere('NOT EXISTS (SELECT r.id FROM '.UserComment::class.' r WHERE r.parent_id = c.id AND r.is_deleted = false)')
      ->setParameter('cutoff', new \DateTime(self::COMMENT_RETENTION))
      ->getQuery()
      ->getSingleColumnResult());
  }
}
