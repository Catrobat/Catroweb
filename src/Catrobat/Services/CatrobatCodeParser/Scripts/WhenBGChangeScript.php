<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class WhenBGChangeScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_BG_CHANGE_SCRIPT;
    $this->caption = 'When background changes to _';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
