<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class SetTransparencyBrick.
 */
class SetTransparencyBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::SET_TRANSPARENCY_BRICK;
    $this->caption = 'Set transparency to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
