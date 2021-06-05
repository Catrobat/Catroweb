<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\DescriptionTooLongException;
use App\Catrobat\Services\ExtractedCatrobatFile;
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
      throw new DescriptionTooLongException();
    }
  }
}
