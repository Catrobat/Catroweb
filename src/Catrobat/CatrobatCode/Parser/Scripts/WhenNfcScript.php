<?php

namespace App\Catrobat\CatrobatCode\Parser\Scripts;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WhenNfcScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_NFC_SCRIPT;
    $this->caption = 'When NFC';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
