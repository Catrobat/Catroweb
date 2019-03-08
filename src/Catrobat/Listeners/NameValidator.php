<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Services\RudeWordFilter;
use App\Catrobat\Exceptions\Upload\MissingProgramNameException;
use App\Catrobat\Exceptions\Upload\NameTooLongException;
use App\Catrobat\Exceptions\Upload\RudewordInNameException;
use App\Catrobat\StatusCode;

/**
 * Class NameValidator
 * @package App\Catrobat\Listeners
 */
class NameValidator
{

  /**
   * @var RudeWordFilter
   */
  private $rudeWordFilter;

  /**
   * NameValidator constructor.
   *
   * @param RudeWordFilter $rudeWordFilter
   */
  public function __construct(RudeWordFilter $rudeWordFilter)
  {
    $this->rudeWordFilter = $rudeWordFilter;
  }

  /**
   * @param ProgramBeforeInsertEvent $event
   *
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event)
  {
    $this->validate($event->getExtractedFile());
  }

  /**
   * @param ExtractedCatrobatFile $file
   *
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  public function validate(ExtractedCatrobatFile $file)
  {
    if ($file->getName() == null || $file->getName() == '')
    {
      throw new MissingProgramNameException();
    }
    elseif (strlen($file->getName()) > StatusCode::OK)
    {
      throw new NameTooLongException();
    }

    if ($this->rudeWordFilter->containsRudeWord($file->getName()))
    {
      throw new RudewordInNameException();
    }
  }
}
