<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class TurnLeftBrick.
 */
class TurnLeftBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::TURN_LEFT_BRICK;
    $this->caption = 'Turn left _ degrees';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
