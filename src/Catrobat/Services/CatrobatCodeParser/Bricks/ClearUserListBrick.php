<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class ClearUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLEAR_LIST_BRICK;
    $this->caption = 'Clear User List';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
