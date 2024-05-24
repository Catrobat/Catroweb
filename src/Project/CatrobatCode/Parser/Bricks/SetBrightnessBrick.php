<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class SetBrightnessBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::SET_BRIGHTNESS_BRICK;
    $this->caption = 'Set brightness to _ %';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
