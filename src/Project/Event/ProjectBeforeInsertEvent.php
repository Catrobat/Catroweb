<?php

declare(strict_types=1);

namespace App\Project\Event;

use App\Project\CatrobatFile\ExtractedCatrobatFile;
use Symfony\Contracts\EventDispatcher\Event;

class ProjectBeforeInsertEvent extends Event
{
  public function __construct(protected ExtractedCatrobatFile $extracted_file, protected bool $restore = false)
  {
  }

  public function getExtractedFile(): ExtractedCatrobatFile
  {
    return $this->extracted_file;
  }

  /** A restore of lost files: the archive was accepted once, so upload-time version rules do not apply. */
  public function isRestore(): bool
  {
    return $this->restore;
  }
}
