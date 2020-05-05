<?php

namespace App\Commands\Reset;

use App\Entity\MigrationManager;
use Doctrine\DBAL\DBALException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DropMigrationCommand extends Command
{
  protected static $defaultName = 'catrobat:drop:migration';
  private OutputInterface $output;

  private MigrationManager $migration_manager;

  public function __construct(MigrationManager $migration_manager)
  {
    parent::__construct();
    $this->migration_manager = $migration_manager;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:drop:migration')
      ->setDescription('Dropping the migration_versions table')
    ;
  }

  /**
   * @throws DBALException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->output = $output;
    if ($this->migration_manager->dropMigrationVersions())
    {
      $this->output->writeln('Table migration_versions dropped!');
    }
    else
    {
      $this->output->writeln('Table migration_versions doesn\'t exist!');
    }

    return 0;
  }
}
