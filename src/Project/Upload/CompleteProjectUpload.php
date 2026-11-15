<?php

declare(strict_types=1);

namespace App\Project\Upload;

use App\System\BackgroundJob\TrackedJobMessage;

final readonly class CompleteProjectUpload implements TrackedJobMessage
{
  public const string JOB_TYPE = 'project_upload';

  public function __construct(
    public string $job_id,
    public string $upload_id,
    public string $user_id,
    public string $accept_language,
    public ?string $ip,
  ) {
  }

  #[\Override]
  public function jobId(): string
  {
    return $this->job_id;
  }
}
