<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PointInDirectionBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::POINT_IN_DIRECTION_BRICK;
    $this->caption = 'Point in direction _ degrees';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
