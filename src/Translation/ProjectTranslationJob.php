<?php

declare(strict_types=1);

namespace App\Translation;

enum ProjectTranslationJob
{
  case Queued;
  // The last attempt failed; the next request queues a new one.
  case Failed;
  // The last attempt found no model for the source or target language; reported once, like Failed.
  case Unsupported;
  // The caller did not allow a new job (rate limit).
  case Refused;
}
