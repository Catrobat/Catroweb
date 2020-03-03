<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class TurnRightBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::TURN_RIGHT_BRICK;
    $this->caption = 'Turn right _ degrees';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
