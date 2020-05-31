<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class WhenNfcScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_NFC_SCRIPT;
    $this->caption = 'When NFC';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
