<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class TurnRightBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::TURN_RIGHT_BRICK;
    $this->caption = 'Turn right _ degrees';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
