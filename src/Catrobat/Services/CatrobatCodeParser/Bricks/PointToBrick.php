<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class PointToBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::POINT_TO_BRICK;
    $this->caption = 'Point towards _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
