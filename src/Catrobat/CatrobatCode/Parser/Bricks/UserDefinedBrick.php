<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class UserDefinedBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::USER_DEFINED_BRICK;
    $this->caption = 'Your Brick';
    $this->setImgFile(Constants::YOUR_BRICK_IMG);
  }
}
