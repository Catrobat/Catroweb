<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater;

use App\DB\Entity\Flavor;
use App\DB\EntityRepository\FlavorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:update:flavors', description: 'Inserting our static flavors into the Database')]
class UpdateFlavorsCommand extends Command
{
  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly FlavorRepository $flavor_repository)
  {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $count = 0;

    foreach (Flavor::ALL as $flavor_name) {
      $flavor = $this->getOrCreateFlavor($flavor_name);
      $this->entity_manager->persist($flavor);
      ++$count;
    }

    $this->entity_manager->flush();
    $output->writeln("{$count} Flavors in the Database have been inserted/updated");

    return 0;
  }

  protected function getOrCreateFlavor(string $name): Flavor
  {
    $flavor = $this->flavor_repository->findOneBy(['name' => $name]) ?? new Flavor();

    return $flavor->setName($name);
  }
}
