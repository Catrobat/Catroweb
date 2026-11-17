<?php

declare(strict_types=1);

namespace App\Translation;

final readonly class TranslateProject
{
  public function __construct(public string $project_id, public ?string $source_language, public string $target_language)
  {
  }
}
