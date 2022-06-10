<?php

namespace App\Project\CatrobatFile;

use App\Api\Services\Projects\ProjectsRequestValidator;
use App\Project\Event\ProgramBeforeInsertEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DescriptionValidatorEventSubscriber implements EventSubscriberInterface
{
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    if (strlen($file->getDescription()) > ProjectsRequestValidator::MAX_DESCRIPTION_LENGTH) {
      throw new InvalidCatrobatFileException('errors.description.toolong', 527);
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
