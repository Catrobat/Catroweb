<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportState;
use App\Moderation\AgeSignalDetector;
use App\Moderation\AutoModerationService;
use App\Moderation\ScanContentImages;
use App\Moderation\ScanContentImagesHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Backfill for the checks new content gets (docs/operations/Content-Moderation.md): lists visible projects, studios
 * and profiles with adults-only texts; --apply hides projects and studios for review and queues profiles (accounts
 * are never hidden). Content with a rejected report is skipped. --images also scans images (slow).
 */
#[AsCommand(
  name: 'catro:moderation:flag-adult-content',
  description: 'Find visible content with adults-only texts (and, with --images, NSFW images); dry run unless --apply',
)]
class FlagAdultContentCommand extends Command
{
  private const int BATCH_SIZE = 500;

  /** @var array<string, true> "type:id" of content with a rejected report */
  private array $reviewed = [];

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly AgeSignalDetector $detector,
    private readonly AutoModerationService $auto_moderation,
    private readonly ScanContentImagesHandler $image_scanner,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('apply', null, InputOption::VALUE_NONE, 'Hide hits and queue them for review (default: only list them)')
      ->addOption('images', null, InputOption::VALUE_NONE, 'Also scan project and studio images with the NSFW scanner (slow)')
      ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'At most this many projects/studios get an image scan', '1000')
      ->addOption('delay', null, InputOption::VALUE_REQUIRED, 'Milliseconds to wait between image scans', '200')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $apply = true === $input->getOption('apply');
    if (!$apply) {
      $io->note('DRY RUN: nothing is hidden. Pass --apply to flag the hits.');
    }

    // An admin who rejected a report about the content has looked at it: don't queue it again.
    /** @var list<array{content_type: string, content_id: string}> $rejected */
    $rejected = $this->entity_manager->createQuery('SELECT r.content_type, r.content_id FROM '.ContentReport::class.' r WHERE r.state = :rejected')
      ->setParameter('rejected', ReportState::Rejected->value)->getArrayResult()
    ;
    foreach ($rejected as $report) {
      $this->reviewed[$report['content_type'].':'.$report['content_id']] = true;
    }

    $hits = 0;
    $hits += $this->scanTexts($io, $apply, ContentType::Project, Project::class, 'auto_hidden', ['name', 'description', 'credits']);
    $hits += $this->scanTexts($io, $apply, ContentType::Studio, Studio::class, 'auto_hidden', ['name', 'description']);
    $hits += $this->scanTexts($io, $apply, ContentType::User, User::class, 'profile_hidden', ['username', 'about', 'currently_working_on']);
    $io->success(sprintf('%d text hits%s. Accounts are never hidden: their profile texts are cleared and queued.', $hits, $apply ? ' flagged' : ''));

    if ($input->getOption('images')) {
      $this->scanImages($io, $apply, (int) $input->getOption('limit'), (int) $input->getOption('delay'));
    }

    return Command::SUCCESS;
  }

  /**
   * @param class-string $entity
   * @param list<string> $fields
   */
  private function scanTexts(SymfonyStyle $io, bool $apply, ContentType $type, string $entity, string $hidden_field, array $fields): int
  {
    $io->section($type->value);
    $hits = 0;
    $last_id = '';
    while (true) {
      /** @var list<array<string, string|null>> $rows */
      $rows = $this->entity_manager->createQuery(
        'SELECT e.id, e.'.implode(', e.', $fields).' FROM '.$entity.' e WHERE e.'.$hidden_field.' = false AND e.id > :last ORDER BY e.id'
      )->setParameter('last', $last_id)->setMaxResults(self::BATCH_SIZE)->getArrayResult();
      if ([] === $rows) {
        return $hits;
      }

      foreach ($rows as $row) {
        $last_id = (string) $row['id'];
        if (isset($this->reviewed[$type->value.':'.$last_id])) {
          continue;
        }

        $texts = array_map(static fn (string $field): ?string => $row[$field], $fields);
        $signal = $this->detector->findSignal(...$texts);
        if (null === $signal) {
          continue;
        }

        ++$hits;
        $io->writeln(sprintf('  %s %s: "%s" in "%s"', $type->value, $last_id, $signal, mb_substr(implode(' | ', array_filter($texts)), 0, 120)));
        if (!$apply) {
          continue;
        }

        if (ContentType::User === $type) {
          $user = $this->entity_manager->find(User::class, $last_id);
          if (null !== $user) {
            $this->auto_moderation->flagUserProfile($user);
          }
        } else {
          $this->auto_moderation->flagAgeSignal($type, $last_id, ...$texts);
        }
      }

      $this->entity_manager->clear();
    }
  }

  private function scanImages(SymfonyStyle $io, bool $apply, int $limit, int $delay_ms): void
  {
    $hits = 0;
    foreach ([[ContentType::Project, Project::class], [ContentType::Studio, Studio::class]] as [$type, $entity]) {
      /** @var list<string> $ids */
      $ids = $this->entity_manager->createQuery('SELECT e.id FROM '.$entity.' e WHERE e.auto_hidden = false ORDER BY e.id DESC')
        ->setMaxResults($limit)->getSingleColumnResult()
      ;
      $io->section(sprintf('Scanning images of %d %ss', count($ids), $type->value));
      foreach ($ids as $id) {
        if (isset($this->reviewed[$type->value.':'.$id])) {
          continue;
        }

        // Calls the handler directly, so the backfill doesn't flood the default queue.
        $hit = $this->image_scanner->scan(new ScanContentImages($type->value, $id), $apply);
        if (null !== $hit) {
          ++$hits;
          $io->writeln(sprintf('  %s %s: %s', $type->value, $id, $hit));
        }

        $this->entity_manager->clear();
        usleep($delay_ms * 1000);
      }
    }

    $io->success(sprintf('%d image hits%s. A scanner outage counts as no hit; see the log.', $hits, $apply ? ' flagged' : ''));
  }
}
