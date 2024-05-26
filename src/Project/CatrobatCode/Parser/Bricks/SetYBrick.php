<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetYBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_Y_BRICK;
    $this->caption = 'Set Y to _';

    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
