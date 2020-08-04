<?php

namespace App\Catrobat\CatrobatCode\Parser\Scripts;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WhenTouchScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_TOUCH_SCRIPT;
    $this->caption = 'When screen is touched';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
