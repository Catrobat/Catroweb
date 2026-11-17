<?php

declare(strict_types=1);

namespace App\Translation;

enum ProjectTranslationJob
{
  case Queued;
  // The last attempt failed; the next request queues a new one.
  case Failed;
  // The caller did not allow a new job (rate limit).
  case Refused;
}
