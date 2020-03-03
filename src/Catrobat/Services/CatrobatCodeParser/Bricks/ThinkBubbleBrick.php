<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ThinkBubbleBrick.
 */
class ThinkBubbleBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::THINK_BUBBLE_BRICK;
    $this->caption = 'Think _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
