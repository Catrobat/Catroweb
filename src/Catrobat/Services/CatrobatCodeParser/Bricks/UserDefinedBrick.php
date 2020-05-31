<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class UserDefinedBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::USER_DEFINED_BRICK;
    $this->caption = 'Your Brick';
    $this->setImgFile(Constants::YOUR_BRICK_IMG);
  }
}
