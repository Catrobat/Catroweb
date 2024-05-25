<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetFrictionBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_FRICTION_BRICK;
    $this->caption = 'Set friction to _ %';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
