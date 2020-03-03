<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class NextLookBrick.
 */
class NextLookBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::NEXT_LOOK_BRICK;
    $this->caption = 'Next look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
