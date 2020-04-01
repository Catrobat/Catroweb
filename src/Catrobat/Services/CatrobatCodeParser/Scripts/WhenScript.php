<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Scripts;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class WhenScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_SCRIPT;
    $this->caption = 'When tapped';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
