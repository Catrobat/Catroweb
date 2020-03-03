<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ChangeTransparencyByNBrick.
 */
class ChangeTransparencyByNBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CHANGE_TRANSPARENCY_BY_N_BRICK;
    $this->caption = 'Change transparency by _';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
