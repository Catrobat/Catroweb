<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\Api\Services\Projects\ProjectsRequestValidator;
use App\Project\Event\ProjectBeforeInsertEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ProjectBeforeInsertEvent::class, method: 'onProjectBeforeInsert')]
class DescriptionValidatorEventListener
{
  public function onProjectBeforeInsert(ProjectBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  public function validate(ExtractedCatrobatFile $file): void
  {
    if (strlen($file->getDescription()) > ProjectsRequestValidator::MAX_DESCRIPTION_LENGTH) {
      throw new InvalidCatrobatFileException('errors.description.toolong', 527);
    }
  }
}
