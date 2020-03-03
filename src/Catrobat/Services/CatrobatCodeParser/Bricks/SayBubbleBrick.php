<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SayBubbleBrick.
 */
class SayBubbleBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SAY_BUBBLE_BRICK;
    $this->caption = 'Say _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
