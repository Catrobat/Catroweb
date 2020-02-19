<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetRotationStyleBrick.
 */
class SetRotationStyleBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_ROTATION_STYLE_BRICK;
    $this->caption = 'Set rotation style';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
