<?php

namespace App\Catrobat\CatrobatCode\Parser\Scripts;

use App\Catrobat\CatrobatCode\Parser\Constants;

class WhenConditionScript extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_CONDITION_SCRIPT;
    $this->caption = 'When _ becomes true';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
