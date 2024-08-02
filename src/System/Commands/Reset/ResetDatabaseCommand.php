<?php

declare(strict_types=1);

namespace App\System\Commands\Reset;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:db:reset', description: 'Reset test database')]
class ResetDatabaseCommand extends Command
{
  public function __construct(protected EntityManagerInterface $entity_manager)
  {
    parent::__construct();
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $entity_manager = $this->entity_manager;
    $metaData = $entity_manager->getMetadataFactory()->getAllMetadata();
    $tool = new SchemaTool($entity_manager);
    $tool->dropSchema($metaData);
    $tool->createSchema($metaData);

    return 0;
  }
}
