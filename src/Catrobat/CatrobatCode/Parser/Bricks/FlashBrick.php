<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class FlashBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FLASH_BRICK;
    $this->caption = 'Turn flashlight _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
