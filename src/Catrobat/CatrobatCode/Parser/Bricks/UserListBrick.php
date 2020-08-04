<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class UserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::USER_LIST_BRICK;
    $this->caption = 'User List';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
