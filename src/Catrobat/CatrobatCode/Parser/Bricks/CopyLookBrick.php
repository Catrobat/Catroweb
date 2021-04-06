<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class CopyLookBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::COPY_LOOK_BRICK;
    $this->caption = 'Copy look and name it';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
