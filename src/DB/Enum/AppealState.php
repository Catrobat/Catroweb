<?php

declare(strict_types=1);

namespace App\DB\Enum;

enum AppealState: int
{
  case Pending = 1;
  case Approved = 2;
  case Rejected = 3;
}
