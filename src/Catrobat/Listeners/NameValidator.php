<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\MissingProgramNameException;
use App\Catrobat\Exceptions\Upload\NameTooLongException;
use App\Catrobat\Exceptions\Upload\RudewordInNameException;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;
use App\Catrobat\StatusCode;

/**
 * Class NameValidator.
 */
class NameValidator
{
  /**
   * @var RudeWordFilter
   */
  private $rudeWordFilter;

  /**
   * NameValidator constructor.
   */
  public function __construct(RudeWordFilter $rudeWordFilter)
  {
    $this->rudeWordFilter = $rudeWordFilter;
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
    if (null == $file->getName() || '' == $file->getName())
    {
      throw new MissingProgramNameException();
    }
    if (strlen($file->getName()) > StatusCode::OK)
    {
      throw new NameTooLongException();
    }

    if ($this->rudeWordFilter->containsRudeWord($file->getName()))
    {
      throw new RudewordInNameException();
    }
  }
}
