<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\Translation\CommentMachineTranslation;
use App\DB\Entity\Translation\ProjectMachineTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:translation:trim-storage', description: 'Clean up old db entries in machine translation schema')]
class TranslationTrimStorageCommand extends Command
{
  private const string OLDER_THAN = 'older-than';

  private const string ONLY_PROJECT = 'only-project';

  private const string ONLY_COMMENT = 'only-comment';

  public function __construct(private readonly EntityManagerInterface $entity_manager)
  {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption(self::OLDER_THAN, null, InputOption::VALUE_REQUIRED,
        'delete entries older than the specified days',
        '30')
      ->addOption(self::ONLY_PROJECT, null, InputOption::VALUE_NONE,
        'only trim project entries')
      ->addOption(self::ONLY_COMMENT, null, InputOption::VALUE_NONE,
        'only trim comment entries')
    ;
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $days = intval($input->getOption(self::OLDER_THAN));

    if ($days < 1) {
      $output->writeln('Specified number of days must be greater than 0');

      return 1;
    }

    $today = new \DateTimeImmutable();
    $date = $today->sub(new \DateInterval(sprintf('P%dD', $days)));

    if ($input->getOption(self::ONLY_PROJECT) && $input->getOption(self::ONLY_COMMENT)) {
      $output->writeln('invalid combination of options');

      return 1;
    }

    if ($input->getOption(self::ONLY_PROJECT)) {
      $this->deleteEntries(ProjectMachineTranslation::class, $date);
    } elseif ($input->getOption(self::ONLY_COMMENT)) {
      $this->deleteEntries(CommentMachineTranslation::class, $date);
    } else {
      $this->deleteEntries(ProjectMachineTranslation::class, $date);
      $this->deleteEntries(CommentMachineTranslation::class, $date);
    }

    return 0;
  }

  private function deleteEntries(string $entity, \DateTimeImmutable $older_than_date): void
  {
    $qb = $this->entity_manager->createQueryBuilder();

    $qb->delete($entity, 'e')
      ->where($qb->expr()->lt('e.last_modified_at', ':date'))
      ->setParameter('date', $older_than_date)
      ->getQuery()
      ->execute()
    ;
  }
}
