<?php

declare(strict_types=1);

namespace App\User\Notification;

enum EmailNotificationPreference: string
{
  case IMMEDIATE = 'immediate';
  case DAILY = 'daily';
  case WEEKLY = 'weekly';
  case NONE = 'none';
}
