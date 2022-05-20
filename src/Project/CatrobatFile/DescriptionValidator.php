<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;

class DescriptionValidator
{
  private int $max_description_size = 10_000;

  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    if (strlen($file->getDescription()) > $this->max_description_size) {
      throw new InvalidCatrobatFileException('errors.description.toolong', 527);
    }
  }
}
