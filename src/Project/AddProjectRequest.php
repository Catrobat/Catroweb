<?php

declare(strict_types=1);

namespace App\Project;

use App\DB\Entity\Flavor;
use App\DB\Entity\User\User;
use Symfony\Component\HttpFoundation\File\File;

class AddProjectRequest
{
  public function __construct(private User $user, private File $project_file, private readonly ?string $ip = '127.0.0.1', private ?string $language = null, private readonly ?string $flavor = Flavor::POCKETCODE)
  {
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUser(User $user): void
  {
    $this->user = $user;
  }

  public function getProjectFile(): File
  {
    return $this->project_file;
  }

  public function setProjectFile(File $project_file): void
  {
    $this->project_file = $project_file;
  }

  public function getIp(): ?string
  {
    return $this->ip;
  }

  public function getLanguage(): ?string
  {
    return $this->language;
  }

  public function setLanguage(?string $language): void
  {
    $this->language = $language;
  }

  public function getFlavor(): ?string
  {
    return $this->flavor;
  }
}
