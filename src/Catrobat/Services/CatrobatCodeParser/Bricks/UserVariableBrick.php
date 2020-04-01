<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class UserVariableBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::USER_VARIABLE_BRICK;
    $this->caption = 'User Variable';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
