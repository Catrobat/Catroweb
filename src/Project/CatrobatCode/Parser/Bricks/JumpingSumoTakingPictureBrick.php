<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class JumpingSumoTakingPictureBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_TAKING_PICTURE_BRICK;
    $this->caption = 'Take a picture';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
