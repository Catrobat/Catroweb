<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ClearGraphicEffectBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLEAR_GRAPHIC_EFFECT_BRICK;
    $this->caption = 'Clear graphic effects';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
