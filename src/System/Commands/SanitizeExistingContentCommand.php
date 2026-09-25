<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\User;
use App\Moderation\TextSanitizer;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
  name: 'catro:moderation:sanitize-existing',
  description: 'Sanitize existing user-generated content (profanity, contact info)',
)]
class SanitizeExistingContentCommand extends Command
{
  private const int BATCH_SIZE = 1000;

  public function __construct(
    private readonly TextSanitizer $textSanitizer,
    private readonly Connection $connection,
    private readonly EntityManagerInterface $entity_manager,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be changed without writing');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dryRun = $input->getOption('dry-run');

    if ($dryRun) {
      $io->note('DRY RUN — no changes will be written');
    }

    $total_sanitized = 0;
    $total_sanitized += $this->sanitizeTable($io, $this->tableOf(Project::class), ['name', 'description', 'credits'], 'id', $dryRun);
    $total_sanitized += $this->sanitizeTable($io, $this->tableOf(UserComment::class), ['text'], 'id', $dryRun);
    $total_sanitized += $this->sanitizeTable($io, $this->tableOf(Studio::class), ['name', 'description'], 'id', $dryRun);
    $total_sanitized += $this->sanitizeTable($io, $this->tableOf(User::class), ['about', 'currently_working_on'], 'id', $dryRun);

    $io->success(sprintf('Done. %d rows sanitized across all tables.', $total_sanitized));

    return Command::SUCCESS;
  }

  /**
   * Table names come from the Doctrine mapping: they differ from the entity names (projects live
   * in "program") and Behat creates the schema from the mapping too.
   *
   * @param class-string $entity_class
   */
  private function tableOf(string $entity_class): string
  {
    return $this->entity_manager->getClassMetadata($entity_class)->getTableName();
  }

  /**
   * @param string[] $columns
   */
  private function sanitizeTable(SymfonyStyle $io, string $table, array $columns, string $pk, bool $dryRun): int
  {
    $total = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM {$table}");
    $io->section(sprintf('[%s] Processing %d rows (%s)', $table, $total, implode(', ', $columns)));

    $sanitized = 0;
    $processed = 0;
    $lastId = null;

    while (true) {
      $select_cols = implode(', ', array_merge([$pk], $columns));
      if (null === $lastId) {
        $rows = $this->connection->fetchAllAssociative(
          "SELECT {$select_cols} FROM {$table} ORDER BY {$pk} LIMIT ?",
          [self::BATCH_SIZE],
        );
      } else {
        $rows = $this->connection->fetchAllAssociative(
          "SELECT {$select_cols} FROM {$table} WHERE {$pk} > ? ORDER BY {$pk} LIMIT ?",
          [$lastId, self::BATCH_SIZE],
        );
      }

      if ([] === $rows) {
        break;
      }

      foreach ($rows as $row) {
        $updates = [];
        $params = [];

        foreach ($columns as $col) {
          $original = $row[$col];
          if (null === $original || '' === $original) {
            continue;
          }

          $sanitized_text = $this->textSanitizer->sanitizeWithLocale($original, 'en');
          if ($sanitized_text !== $original) {
            $updates[] = "{$col} = ?";
            $params[] = $sanitized_text;
          }
        }

        if ([] !== $updates) {
          if ($dryRun) {
            $io->writeln(sprintf('  [%s] id=%s would be sanitized', $table, $row[$pk]));
          } else {
            $params[] = $row[$pk];
            $this->connection->executeStatement(
              "UPDATE {$table} SET ".implode(', ', $updates)." WHERE {$pk} = ?",
              $params,
            );
          }
          ++$sanitized;
        }

        $lastId = $row[$pk];
      }

      $processed += count($rows);
      $io->writeln(sprintf('  Processed %d/%d, %d sanitized so far', $processed, $total, $sanitized));
    }

    return $sanitized;
  }
}
