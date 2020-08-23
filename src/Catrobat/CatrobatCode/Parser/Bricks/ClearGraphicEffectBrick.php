<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ClearGraphicEffectBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLEAR_GRAPHIC_EFFECT_BRICK;
    $this->caption = 'Clear graphic effects';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
