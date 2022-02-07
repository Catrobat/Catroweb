<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;
use Symfony\Component\HttpFoundation\Response;

class NameValidator
{
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    if ('' == $file->getName()) {
      throw new InvalidCatrobatFileException('errors.name.missing', 509);
    }
    if (strlen($file->getName()) > Response::HTTP_OK) {
      throw new InvalidCatrobatFileException('errors.name.toolong', 526);
    }
  }
}
