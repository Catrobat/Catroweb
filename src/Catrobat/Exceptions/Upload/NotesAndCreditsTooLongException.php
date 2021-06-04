<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class NotesAndCreditsTooLongException extends InvalidCatrobatFileException
{
  public function __construct()
  {
    parent::__construct('errors.notesAndCredits.toolong', StatusCode::NOTES_AND_CREDITS_TOO_LONG);
  }
}
