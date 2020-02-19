<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;

/**
 * Class WhenTouchBrick.
 */
class WhenTouchBrick extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_TOUCH_BRICK;
    $this->caption = 'When screen is touched';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
