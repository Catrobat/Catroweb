<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\ProgramBeforeInsertEvent;
use App\Catrobat\Exceptions\Upload\NotesAndCreditsTooLongException;
use App\Catrobat\Exceptions\Upload\RudeWordInNotesAndCreditsException;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\RudeWordFilter;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class NotesAndCreditsValidator
{
  private RudeWordFilter $rudeWordFilter;
  private int $max_notes_and_credits_size;

  public function __construct(RudeWordFilter $rudeWordFilter)
  {
    $this->rudeWordFilter = $rudeWordFilter;
    $this->max_notes_and_credits_size = 3_000;
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
    if (strlen($file->getNotesAndCredits()) > $this->max_notes_and_credits_size)
    {
      throw new NotesAndCreditsTooLongException();
    }

    if ($this->rudeWordFilter->containsRudeWord($file->getDescription()))
    {
      throw new RudeWordInNotesAndCreditsException();
    }
  }
}
