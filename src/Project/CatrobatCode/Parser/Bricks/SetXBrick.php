<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetXBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_X_BRICK;
    $this->caption = 'Set X to _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
