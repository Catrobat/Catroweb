<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PointToBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::POINT_TO_BRICK;
    $this->caption = 'Point towards _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
