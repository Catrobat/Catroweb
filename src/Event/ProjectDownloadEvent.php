<?php

namespace App\Event;

use App\Entity\Program;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class ProjectDownloadEvent extends Event
{
  protected ?User $user;
  protected Program $project;
  protected Request $request;

  public function __construct(?User $user, Program $project, Request $request)
  {
    $this->user = $user;
    $this->project = $project;
    $this->request = $request;
  }

  public function getRequest(): Request
  {
    return $this->request;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function getProject(): Program
  {
    return $this->project;
  }
}
