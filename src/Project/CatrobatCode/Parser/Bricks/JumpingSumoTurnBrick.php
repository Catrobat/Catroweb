<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class JumpingSumoTurnBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_TURN_BRICK;
    $this->caption = 'Turn around';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
