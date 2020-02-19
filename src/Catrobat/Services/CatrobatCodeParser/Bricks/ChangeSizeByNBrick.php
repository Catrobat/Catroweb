<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ChangeSizeByNBrick.
 */
class ChangeSizeByNBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CHANGE_SIZE_BY_N_BRICK;
    $this->caption = 'Change size by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
