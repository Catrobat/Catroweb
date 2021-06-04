<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class PointToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::POINT_TO_BRICK;
    $this->caption = 'Point towards _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
