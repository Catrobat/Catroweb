<?php

namespace App\Catrobat\CatrobatCode\Parser\Scripts;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WhenBGChangeScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_BG_CHANGE_SCRIPT;
    $this->caption = 'When background changes to _';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
