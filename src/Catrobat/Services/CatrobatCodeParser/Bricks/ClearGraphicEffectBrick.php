<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ClearGraphicEffectBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLEAR_GRAPHIC_EFFECT_BRICK;
    $this->caption = 'Clear graphic effects';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
