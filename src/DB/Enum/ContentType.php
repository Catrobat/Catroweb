<?php

declare(strict_types=1);

namespace App\DB\Enum;

enum ContentType: string
{
  case Project = 'project';
  case Comment = 'comment';
  case User = 'user';
  case Studio = 'studio';
}
