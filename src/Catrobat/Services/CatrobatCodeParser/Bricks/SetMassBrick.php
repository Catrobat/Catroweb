<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class SetMassBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_MASS_BRICK;
    $this->caption = 'Set mass to _ kilogram';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
