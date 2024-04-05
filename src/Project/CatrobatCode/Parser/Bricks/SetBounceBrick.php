<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetBounceBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_BOUNCE_BRICK;
    $this->caption = 'Set bounce factor to _ %';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
