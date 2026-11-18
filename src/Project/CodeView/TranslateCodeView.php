<?php

declare(strict_types=1);

namespace App\Project\CodeView;

final readonly class TranslateCodeView
{
  public function __construct(
    public string $project_id,
    public string $export_key,
    public ?string $source_language,
    public string $target_language,
    public string $attempt,
  ) {
  }
}
