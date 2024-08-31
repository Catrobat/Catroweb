<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractApiProcessor;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use App\Studio\StudioManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StudioApiProcessor extends AbstractApiProcessor
{
  public function __construct(private readonly StudioManager $studio_manager)
  {
  }

  public function create(?User $user, ?string $name, string $description, bool $is_public, bool $enable_comments, ?UploadedFile $image_file): Studio
  {
    return $this->studio_manager->createStudio(
      $user,
      $name,
      $description,
      $is_public,
      true,
      $enable_comments,
      $image_file
    );
  }

  public function update(Studio $studio, ?string $name, ?string $description, ?bool $is_public, ?bool $enable_comments, ?UploadedFile $image_file): Studio
  {
    return $this->studio_manager->updateStudio(
      $studio,
      $name,
      $description,
      $is_public,
      $enable_comments,
      $image_file
    );
  }

  public function deleteStudio(Studio $studio, User $user): void
  {
    $this->studio_manager->deleteStudio($studio, $user);
  }
}
