<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ChangeXByNBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CHANGE_X_BY_N_BRICK;
    $this->caption = 'Change X by _';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
