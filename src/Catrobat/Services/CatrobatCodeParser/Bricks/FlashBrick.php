<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class FlashBrick.
 */
class FlashBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::FLASH_BRICK;
    $this->caption = 'Turn flashlight _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
