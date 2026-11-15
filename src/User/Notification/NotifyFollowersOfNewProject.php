<?php

declare(strict_types=1);

namespace App\User\Notification;

final readonly class NotifyFollowersOfNewProject
{
  public function __construct(public string $project_id)
  {
  }
}
