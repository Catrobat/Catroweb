<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ShowBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::SHOW_BRICK;
    $this->caption = 'Show';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
