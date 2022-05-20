<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;

class NotesAndCreditsValidator
{
  private int $max_notes_and_credits_size = 3_000;

  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    if (strlen($file->getNotesAndCredits()) > $this->max_notes_and_credits_size) {
      throw new InvalidCatrobatFileException('errors.notesAndCredits.toolong', 707);
    }
  }
}
