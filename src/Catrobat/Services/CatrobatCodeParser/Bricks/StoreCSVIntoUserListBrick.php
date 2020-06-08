<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

class StoreCSVIntoUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STORE_CSV_INTO_USERLIST_BRICK;
    $this->caption = 'Store CSV column into list';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
