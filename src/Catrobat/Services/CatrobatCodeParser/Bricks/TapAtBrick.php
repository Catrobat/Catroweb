<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class TapAtBrick.
 */
class TapAtBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::TAP_AT_BRICK;
    $this->caption = 'Tap At';
    $this->setImgFile(Constants::TESTING_BRICK_IMG);
  }
}
