<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DroneTakeOffLandBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_TAKE_OFF_LAND_BRICK;
    $this->caption = 'Take off or land drone';
    $this->setImgFile(Constants::AR_DRONE_MOTION_BRICK_IMG);
  }
}
