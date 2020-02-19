<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class WhenRaspiPinChangedScript.
 */
class WhenRaspiPinChangedScript extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_RASPI_PIN_CHANGED_SCRIPT;
    $this->caption = 'When Raspberry pin _ changes to _';
    $this->setImgFile(Constants::RASPI_EVENT_SCRIPT_IMG);
  }
}
