<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class SetMassBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SET_MASS_BRICK;
    $this->caption = 'Set mass to _ kilogram';
    $this->setImgFile(Constants::MOTION_BRICK_IMG);
  }
}
