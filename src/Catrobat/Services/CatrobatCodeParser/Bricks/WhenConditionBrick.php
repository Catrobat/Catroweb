<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;

class WhenConditionBrick extends Script
{
  protected function create(): void
  {
    $this->type = Constants::WHEN_CONDITION_BRICK;
    $this->caption = 'When _ becomes true';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
