<?php

namespace App\Project\CatrobatFile;

use App\Api\Services\Projects\ProjectsRequestValidator;
use App\Project\Event\ProgramBeforeInsertEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotesAndCreditsValidatorEventSubscriber implements EventSubscriberInterface
{
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    if (strlen($file->getNotesAndCredits()) > ProjectsRequestValidator::MAX_CREDITS_LENGTH) {
      throw new InvalidCatrobatFileException('errors.notesAndCredits.toolong', 707);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array
  {
    return [ProgramBeforeInsertEvent::class => 'onProgramBeforeInsert'];
  }
}
