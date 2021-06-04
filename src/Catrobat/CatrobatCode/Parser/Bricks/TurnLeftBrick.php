<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class TurnLeftBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::TURN_LEFT_BRICK;
    $this->caption = 'Turn left _ degrees';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
