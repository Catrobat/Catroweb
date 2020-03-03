<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetSizeToBrick.
 */
class SetSizeToBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_SIZE_TO_BRICK;
    $this->caption = 'Set size to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
