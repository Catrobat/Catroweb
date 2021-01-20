<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class JumpingSumoTurnBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_TURN_BRICK;
    $this->caption = 'Turn around';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
