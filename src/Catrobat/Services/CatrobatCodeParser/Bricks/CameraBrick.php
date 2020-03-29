<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class CameraBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CAMERA_BRICK;
    $this->caption = 'Turn camera _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
