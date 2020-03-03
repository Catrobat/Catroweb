<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class LoopEndlessBrick deprecated.
 */
class LoopEndlessBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::LOOP_ENDLESS_BRICK;
    $this->caption = 'LoopEndlessBrick (deprecated)';
    $this->setImgFile(Constants::DEPRECATED_BRICK_IMG);
  }
}
