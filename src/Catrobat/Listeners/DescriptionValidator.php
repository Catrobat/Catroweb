<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\DescriptionTooLongException;
use App\Catrobat\Exceptions\Upload\RudewordInDescriptionException;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class DescriptionValidator
{
  private RudeWordFilter $rudeWordFilter;
  private int $max_description_size;

  public function __construct(RudeWordFilter $rudeWordFilter)
  {
    $this->rudeWordFilter = $rudeWordFilter;
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

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function validate(ExtractedCatrobatFile $file): void
  {
    if (strlen($file->getDescription()) > $this->max_description_size)
    {
      throw new DescriptionTooLongException();
    }

    if ($this->rudeWordFilter->containsRudeWord($file->getDescription()))
    {
      throw new RudewordInDescriptionException();
    }
  }
}
