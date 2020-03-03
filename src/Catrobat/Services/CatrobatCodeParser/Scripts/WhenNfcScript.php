<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class WhenNfcScript.
 */
class WhenNfcScript extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_NFC_SCRIPT;
    $this->caption = 'When NFC';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
