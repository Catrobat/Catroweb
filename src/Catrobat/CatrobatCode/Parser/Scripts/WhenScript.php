<?php

namespace App\Catrobat\CatrobatCode\Parser\Scripts;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WhenScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_SCRIPT;
    $this->caption = 'When tapped';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
