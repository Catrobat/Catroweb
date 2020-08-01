<?php

namespace App\Catrobat\Requests;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

class AddProgramRequest
{
  private User $user;

  private File $program_file;

  private string $ip;

  private ?string $language;

  private string $flavor;

  public function __construct(User $user, File $program_file, ?string $ip = '127.0.0.1',
                              ?string $language = null, ?string $flavor = 'pocketcode')
  {
    $this->user = $user;
    $this->program_file = $program_file;
    $this->ip = $ip;
    $this->language = $language;
    $this->flavor = $flavor;
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

  public function getIp(): string
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

  public function getFlavor(): string
  {
    return $this->flavor;
  }
}
