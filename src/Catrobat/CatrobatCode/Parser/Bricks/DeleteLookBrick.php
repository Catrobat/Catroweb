<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class DeleteLookBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::DELETE_LOOK_BRICK;
    $this->caption = 'Delete look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
