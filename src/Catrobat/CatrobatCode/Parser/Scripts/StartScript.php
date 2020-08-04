<?php

namespace App\Catrobat\CatrobatCode\Parser\Scripts;

use App\Catrobat\CatrobatCode\Parser\Constants;

class StartScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::START_SCRIPT;
    $this->caption = 'When program started';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
