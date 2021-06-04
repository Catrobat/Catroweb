<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;
use App\Catrobat\CatrobatCode\Parser\Scripts\Script;

class WhenRaspiPinChangedBrick extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_RASPI_PIN_CHANGED_BRICK;
    $this->caption = 'When Raspberry pin _ changes to _';
    $this->setImgFile(Constants::RASPI_EVENT_SCRIPT_IMG);
  }
}
