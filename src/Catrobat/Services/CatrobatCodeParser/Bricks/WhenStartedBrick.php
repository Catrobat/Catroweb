<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;

/**
 * Class WhenStartedBrick.
 */
class WhenStartedBrick extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_STARTED_BRICK;
    $this->caption = 'When program started';
    $this->setImgFile(Constants::EVENT_SCRIPT_IMG);
  }
}
