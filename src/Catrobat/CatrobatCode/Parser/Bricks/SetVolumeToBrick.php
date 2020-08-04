<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetVolumeToBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_VOLUME_TO_BRICK;
    $this->caption = 'Set volume to _ %';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
