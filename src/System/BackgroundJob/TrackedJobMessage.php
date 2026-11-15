<?php

declare(strict_types=1);

namespace App\System\BackgroundJob;

/** A message whose progress a client polls: it names its BackgroundJob row. */
interface TrackedJobMessage
{
  public function jobId(): string;
}
