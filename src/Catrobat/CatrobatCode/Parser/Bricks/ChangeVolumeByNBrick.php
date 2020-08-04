<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ChangeVolumeByNBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CHANGE_VOLUME_BY_N_BRICK;
    $this->caption = 'Change volume by _';
    $this->setImgFile(Constants::SOUND_BRICK_IMG);
  }
}
