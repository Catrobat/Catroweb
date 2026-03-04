<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractApiLoader;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioUser;
use App\DB\Entity\User\User;
use App\Studio\StudioManager;

class StudioApiLoader extends AbstractApiLoader
{
  public function __construct(private readonly StudioManager $studio_manager)
  {
  }

  public function loadStudioByID(string $id): ?Studio
  {
    return $this->studio_manager->findStudioById($id);
  }

  public function loadVisibleStudio(string $id): ?Studio
  {
    $studio = $this->studio_manager->findStudioById($id);

    if (null === $studio || $studio->getAutoHidden()) {
      return null;
    }

    return $studio;
  }

  public function loadStudioUser(?User $user, Studio $studio): ?StudioUser
  {
    return $this->studio_manager->findStudioUser($user, $studio);
  }
}
