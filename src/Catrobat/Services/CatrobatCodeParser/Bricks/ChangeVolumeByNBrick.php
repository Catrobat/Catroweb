<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ChangeVolumeByNBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CHANGE_VOLUME_BY_N_BRICK;
    $this->caption = 'Change volume by _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
