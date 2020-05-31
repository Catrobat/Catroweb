<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ChangeSizeByNBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CHANGE_SIZE_BY_N_BRICK;
    $this->caption = 'Change size by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
