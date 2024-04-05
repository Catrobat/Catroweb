<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class PhiroRgbLightBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::PHIRO_RGB_LIGHT_BRICK;
    $this->caption = 'Set Phiro light';
    $this->setImgFile(Constants::PHIRO_LOOK_BRICK_IMG);
  }
}
