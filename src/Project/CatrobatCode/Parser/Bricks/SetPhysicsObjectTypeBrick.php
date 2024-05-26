<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetPhysicsObjectTypeBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_PHYSICS_OBJECT_TYPE_BRICK;
    $this->caption = 'Set motion type to _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
