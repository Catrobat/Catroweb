<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PointInDirectionBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::POINT_IN_DIRECTION_BRICK;
    $this->caption = 'Point in direction _ degrees';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
