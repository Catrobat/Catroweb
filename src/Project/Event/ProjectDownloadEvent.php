<?php

declare(strict_types=1);

namespace App\Project\Event;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class ProjectDownloadEvent extends Event
{
  protected Request $request;

  public function __construct(protected ?User $user, protected Program $project, protected string $download_type)
  {
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function getProject(): Program
  {
    return $this->project;
  }

  public function getDownloadType(): string
  {
    return $this->download_type;
  }
}
