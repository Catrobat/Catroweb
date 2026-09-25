<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\Moderation\LinkDetector;
use App\User\UserManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Usernames with links are rejected since the link check exists; this renames the accounts
 * created before it, so their advertising stops showing in notifications and on the site.
 */
#[AsCommand(
  name: 'catro:moderation:rename-link-usernames',
  description: 'Rename existing users whose username contains a link',
)]
class RenameLinkUsernamesCommand extends Command
{
  private const int BATCH_SIZE = 1000;

  public function __construct(
    private readonly LinkDetector $link_detector,
    private readonly UserManager $user_manager,
    private readonly Connection $connection,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be renamed without writing');
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = (bool) $input->getOption('dry-run');

    if ($dry_run) {
      $io->note('DRY RUN — no changes will be written');
    }

    $renamed = [];
    $last_id = '';
    do {
      /** @var list<array{id: string, username: ?string}> $rows */
      $rows = $this->connection->fetchAllAssociative(
        'SELECT id, username FROM fos_user WHERE id > ? ORDER BY id LIMIT '.self::BATCH_SIZE,
        [$last_id]
      );

      foreach ($rows as $row) {
        $last_id = $row['id'];
        if (!$this->link_detector->containsLink($row['username'])) {
          continue;
        }

        $new_username = $this->nextFreeUsername();
        $renamed[] = [$row['id'], (string) $row['username'], $new_username];
        if ($dry_run) {
          continue;
        }

        $user = $this->user_manager->find($row['id']);
        if (null === $user) {
          continue;
        }
        $user->setUsername($new_username);
        $this->user_manager->updateUser($user);
      }
    } while (self::BATCH_SIZE === count($rows));

    if ([] !== $renamed) {
      $io->table(['id', 'old username', 'new username'], $renamed);
    }
    $io->success(sprintf('%s %d users.', $dry_run ? 'Would rename' : 'Renamed', count($renamed)));

    return Command::SUCCESS;
  }

  private function nextFreeUsername(): string
  {
    do {
      $username = 'user'.random_int(100_000, 999_999_999);
    } while (null !== $this->user_manager->findUserByUsername($username));

    return $username;
  }
}
