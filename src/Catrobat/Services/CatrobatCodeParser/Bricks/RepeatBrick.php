<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class RepeatBrick.
 */
class RepeatBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::REPEAT_BRICK;
    $this->caption = 'Repeat _ times';
    $this->setImgFile(Constants::CONTROL_BRICK_IMG);
  }
}
