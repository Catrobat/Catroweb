<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class PlaceAtBrick.
 */
class PlaceAtBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::PLACE_AT_BRICK;
    $this->caption = 'Place at X: _ Y: _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
