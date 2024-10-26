<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\Api\Services\Projects\ProjectsRequestValidator;
use App\Project\Event\ProjectBeforeInsertEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ProjectBeforeInsertEvent::class, method: 'onProjectBeforeInsert')]
class NotesAndCreditsValidatorEventListener
{
  public function onProjectBeforeInsert(ProjectBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    if (strlen($file->getNotesAndCredits()) > ProjectsRequestValidator::MAX_CREDITS_LENGTH) {
      throw new InvalidCatrobatFileException('errors.notesAndCredits.toolong', 707);
    }
  }
}
