<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ClearGraphicEffectBrick
 * @package App\Catrobat\Services\CatrobatCodeParser\Bricks
 */
class ClearGraphicEffectBrick extends Brick
{
  /**
   *
   */
  protected function create()
  {
    $this->type = Constants::CLEAR_GRAPHIC_EFFECT_BRICK;
    $this->caption = "Clear graphic effects";

    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}