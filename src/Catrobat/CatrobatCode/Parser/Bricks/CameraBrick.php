<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class CameraBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CAMERA_BRICK;
    $this->caption = 'Turn camera _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
