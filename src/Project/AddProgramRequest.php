<?php

namespace App\Project;

use App\DB\Entity\User\User;
use Symfony\Component\HttpFoundation\File\File;

class AddProgramRequest
{
  public function __construct(private User $user, private File $program_file, private readonly ?string $ip = '127.0.0.1', private ?string $language = null, private readonly ?string $flavor = 'pocketcode')
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

  public function getProgramFile(): File
  {
    return $this->program_file;
  }

  public function setProgramFile(File $program_file): void
  {
    $this->program_file = $program_file;
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
