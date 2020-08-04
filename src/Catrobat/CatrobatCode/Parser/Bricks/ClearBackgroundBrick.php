<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ClearBackgroundBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLEAR_BACKGROUND_BRICK;
    $this->caption = 'Clear';
    $this->setImgFile(Constants::PEN_BRICK_IMG);
  }
}
