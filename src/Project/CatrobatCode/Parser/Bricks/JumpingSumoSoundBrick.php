<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class JumpingSumoSoundBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::JUMP_SUMO_SOUND_BRICK;
    $this->caption = 'Play a sound.';
    $this->setImgFile(Constants::JUMPING_SUMO_BRICK_IMG);
  }
}
