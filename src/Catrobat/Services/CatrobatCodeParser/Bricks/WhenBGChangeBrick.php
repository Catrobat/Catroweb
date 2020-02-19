<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;

/**
 * Class WhenBGChangeBrick.
 */
class WhenBGChangeBrick extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_BG_CHANGE_BRICK;
    $this->caption = 'When background changes to _';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
