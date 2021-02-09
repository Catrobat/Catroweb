<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class EditLookBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::EDIT_LOOK_BRICK;
    $this->caption = 'Edit look';
    $this->setImgFile(Constants::LOOKS_BRICK_IMG);
  }
}
