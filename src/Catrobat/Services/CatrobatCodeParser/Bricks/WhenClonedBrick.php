<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;
use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;

/**
 * Class WhenClonedBrick.
 */
class WhenClonedBrick extends Script
{
  protected function create()
  {
    $this->type = Constants::WHEN_CLONED_BRICK;
    $this->caption = 'When I start as a clone';
    $this->setImgFile(Constants::CONTROL_SCRIPT_IMG);
  }
}
