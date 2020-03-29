<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class PointInDirectionBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::POINT_IN_DIRECTION_BRICK;
    $this->caption = 'Point in direction _ degrees';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
