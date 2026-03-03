<?php

declare(strict_types=1);

namespace App\DB\Enum;

enum ReportState: int
{
  case New = 1;
  case Accepted = 2;
  case Rejected = 3;
}
