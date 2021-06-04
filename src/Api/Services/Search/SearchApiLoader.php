<?php

namespace App\Api\Services\Search;

use App\Api\Services\Base\AbstractApiLoader;
use Doctrine\ORM\EntityManagerInterface;

final class SearchApiLoader extends AbstractApiLoader
{
  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    $this->entity_manager = $entity_manager;
  }
}
