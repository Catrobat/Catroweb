<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DroneSwitchCameraBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::AR_DRONE_SWITCH_CAMERA_BRICK;
    $this->caption = 'Switch Camera';
    $this->setImgFile(Constants::AR_DRONE_LOOKS_BRICK_IMG);
  }
}
