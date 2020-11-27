<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class RudeWordInNotesAndCreditsException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.notesAndCredits.rude', StatusCode::RUDE_WORD_IN_NOTES_AND_CREDITS);
  }
}
