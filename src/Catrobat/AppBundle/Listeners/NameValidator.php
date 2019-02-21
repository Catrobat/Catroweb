<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Events\ProgramBeforeInsertEvent;
use Catrobat\AppBundle\Services\RudeWordFilter;
use Catrobat\AppBundle\Exceptions\Upload\MissingProgramNameException;
use Catrobat\AppBundle\Exceptions\Upload\NameTooLongException;
use Catrobat\AppBundle\Exceptions\Upload\RudewordInNameException;
use Catrobat\AppBundle\StatusCode;

/**
 * Class NameValidator
 * @package Catrobat\AppBundle\Listeners
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
