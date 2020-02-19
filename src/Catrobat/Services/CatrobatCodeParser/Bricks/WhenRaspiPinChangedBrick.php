<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;

/**
 * Class WhenRaspiPinChangedBrick.
 */
class WhenRaspiPinChangedBrick extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_RASPI_PIN_CHANGED_BRICK;
    $this->caption = 'When Raspberry pin _ changes to _';
    $this->setImgFile(Constants::RASPI_EVENT_SCRIPT_IMG);
  }
}
