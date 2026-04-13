<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\Entity\User\User;
use App\Storage\Images\ImageVariantGenerator;
use App\User\UserAvatarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Backfills responsive AVIF/WebP avatar variants for users whose `avatar`
 * column still holds a base64 data URI but whose `avatar_key` is NULL.
 *
 * Idempotent: re-running will skip any user whose `avatar_key` is already
 * populated. Safe to run alongside production traffic — writes go to the
 * filesystem first, then the DB row is updated in a short transaction.
 *
 * Usage:
 *   bin/console catro:migrate:avatars            # migrate in batches of 50
 *   bin/console catro:migrate:avatars --dry-run  # report how many rows would change
 *   bin/console catro:migrate:avatars --batch=20 # tune the flush batch
 */
#[AsCommand(
  name: 'catro:migrate:avatars',
  description: 'Generate responsive avatar variants from the legacy base64 avatar column (#6628).',
)]
class MigrateAvatarsCommand extends Command
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ImageVariantGenerator $image_variant_generator,
    private readonly UserAvatarService $user_avatar_service,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Report work without writing variants or updating the DB.')
      ->addOption('batch', null, InputOption::VALUE_REQUIRED, 'Users per flush.', '50')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = (bool) $input->getOption('dry-run');
    $batch = max(1, (int) $input->getOption('batch'));

    $qb = $this->entity_manager->createQueryBuilder()
      ->select('u')
      ->from(User::class, 'u')
      ->where('u.avatar IS NOT NULL')
      ->andWhere('u.avatar_key IS NULL')
    ;

    $count = (int) (clone $qb)->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();
    if (0 === $count) {
      $io->success('No users to migrate — every legacy avatar already has variants.');

      return Command::SUCCESS;
    }

    $io->note(sprintf('%d user(s) queued. %s', $count, $dry_run ? '(dry-run)' : ''));

    $succeeded = 0;
    $failed = 0;
    $iterable = $qb->getQuery()->toIterable();
    $progress = $io->createProgressBar($count);
    $progress->setFormat('verbose');

    foreach ($iterable as $user) {
      $progress->advance();
      if (!$user instanceof User) {
        continue;
      }

      try {
        if ($dry_run) {
          ++$succeeded;
          continue;
        }

        $this->migrateOne($user);
        ++$succeeded;

        if (0 === $succeeded % $batch) {
          $this->entity_manager->flush();
          $this->entity_manager->clear();
        }
      } catch (\Throwable $e) {
        ++$failed;
        $io->warning(sprintf('User %s: %s', $user->getId() ?? '?', $e->getMessage()));
      }
    }

    if (!$dry_run) {
      $this->entity_manager->flush();
      $this->entity_manager->clear();
    }

    $progress->finish();
    $io->newLine(2);

    if ($failed > 0) {
      $io->warning(sprintf('Done with errors. %d succeeded, %d failed.', $succeeded, $failed));

      return Command::FAILURE;
    }

    $io->success(sprintf('Done. %d user(s) %s.', $succeeded, $dry_run ? 'would be migrated' : 'migrated'));

    return Command::SUCCESS;
  }

  private function migrateOne(User $user): void
  {
    $data_uri = $user->getAvatar();
    if (null === $data_uri || '' === $data_uri) {
      return;
    }

    if (!preg_match('#^data:([^;]+);base64,(.*)$#s', $data_uri, $matches)) {
      throw new \RuntimeException('avatar is not a base64 data URI');
    }

    $decoded = base64_decode((string) $matches[2], true);
    if (false === $decoded) {
      throw new \RuntimeException('base64 decode failed');
    }

    $temp_source = tempnam(sys_get_temp_dir(), 'catroweb-avatar-migrate-');
    if (false === $temp_source) {
      throw new \RuntimeException('could not allocate a temp file');
    }

    try {
      if (false === file_put_contents($temp_source, $decoded)) {
        throw new \RuntimeException('could not write temp file');
      }

      $key = (string) $user->getId().'-'.dechex(random_int(0, 0xFFFFFFFF));
      $this->image_variant_generator->generate(
        $temp_source,
        $this->user_avatar_service->getStorageDir(),
        $key,
      );
      $user->setAvatarKey($key);
    } finally {
      @unlink($temp_source);
    }
  }
}
