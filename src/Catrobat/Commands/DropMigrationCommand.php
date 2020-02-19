<?php

namespace App\Catrobat\Commands;

use App\Entity\MigrationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DropMigrationCommand.
 */
class DropMigrationCommand extends Command
{
  /**
   * @var
   */
  private $output;
  /**
   * @var MigrationManager
   */
  private $migration_manager;

  /**
   * DropMigrationCommand constructor.
   */
  public function __construct(MigrationManager $migration_manager)
  {
    parent::__construct();
    $this->migration_manager = $migration_manager;
  }

  protected function configure()
  {
    $this->setName('catrobat:drop:migration')
      ->setDescription('Dropping the migration_versions table')
    ;
  }

  /**
   * @throws \Doctrine\DBAL\DBALException
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
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
  }
}
