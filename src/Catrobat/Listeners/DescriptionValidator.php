<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\DescriptionTooLongException;
use App\Catrobat\Exceptions\Upload\RudewordInDescriptionException;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;

/**
 * Class DescriptionValidator.
 */
class DescriptionValidator
{
  /**
   * @var RudeWordFilter
   */
  private $rudeWordFilter;
  /**
   * @var int
   */
  private $max_description_size;

  /**
   * DescriptionValidator constructor.
   */
  public function __construct(RudeWordFilter $rudeWordFilter)
  {
    $this->rudeWordFilter = $rudeWordFilter;
    $this->max_description_size = 10000;
  }

  /**
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }

  /**
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function validate(ExtractedCatrobatFile $file)
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
