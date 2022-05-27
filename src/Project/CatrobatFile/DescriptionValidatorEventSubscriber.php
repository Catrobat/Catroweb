<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DescriptionValidatorEventSubscriber implements EventSubscriberInterface
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

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array
  {
    return [ProgramBeforeInsertEvent::class => 'onProgramBeforeInsert'];
  }
}
