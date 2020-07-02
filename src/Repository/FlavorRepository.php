<?php

namespace App\Repository;

use App\Entity\Flavor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FlavorRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Flavor::class);
  }

  /** @noinspection PhpIncompatibleReturnTypeInspection */
  public function getFlavorByName(string $name): ?Flavor
  {
    return $this->findOneBy(['name' => $name]);
  }

  public function getFlavorsByNames(iterable $names): array
  {
    return $this->findBy(['name' => $names]);
  }

  public function getAllFlavors(): array
  {
    return $this->findAll();
  }
}
