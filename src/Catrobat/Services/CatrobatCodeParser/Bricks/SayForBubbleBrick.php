<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SayForBubbleBrick.
 */
class SayForBubbleBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SAY_FOR_BUBBLE_BRICK;
    $this->caption = 'Say _ for _ seconds';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
