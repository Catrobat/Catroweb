<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class GlideToBrick.
 */
class GlideToBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::GLIDE_TO_BRICK;
    $this->caption = 'Glide _ second(s) to X: _ Y: _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
