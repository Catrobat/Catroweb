<?php

declare(strict_types=1);

namespace App\Project\Scratch;

use App\System\BackgroundJob\TrackedJobMessage;

final readonly class ConvertScratchProject implements TrackedJobMessage
{
  public function __construct(public string $job_id, public int $scratch_id, public ?string $ip)
  {
  }

  #[\Override]
  public function jobId(): string
  {
    return $this->job_id;
  }
}
