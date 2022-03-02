<?php

namespace App\Project\CatrobatFile;

use App\Project\Event\ProgramBeforeInsertEvent;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class DescriptionValidator
{
  private int $max_description_size;

  public function __construct()
  {
    $this->max_description_size = 10_000;
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
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
