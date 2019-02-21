<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Constants;

/**
 * Class ClearGraphicEffectBrick
 * @package Catrobat\AppBundle\Services\CatrobatCodeParser\Bricks
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