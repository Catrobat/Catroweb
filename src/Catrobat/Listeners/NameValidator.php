<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\MissingProgramNameException;
use App\Catrobat\Exceptions\Upload\NameTooLongException;
use App\Catrobat\Exceptions\Upload\RudewordInNameException;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Response;

class NameValidator
{
  private RudeWordFilter $rudeWordFilter;

  public function __construct(RudeWordFilter $rudeWordFilter)
  {
    $this->rudeWordFilter = $rudeWordFilter;
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function onProgramBeforeInsert(ProgramBeforeInsertEvent $event): void
  {
    $this->validate($event->getExtractedFile());
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  public function validate(ExtractedCatrobatFile $file): void
  {
    if ('' == $file->getName())
    {
      throw new MissingProgramNameException();
    }
    if (strlen($file->getName()) > Response::HTTP_OK)
    {
      throw new NameTooLongException();
    }

    if ($this->rudeWordFilter->containsRudeWord($file->getName()))
    {
      throw new RudewordInNameException();
    }
  }
}
