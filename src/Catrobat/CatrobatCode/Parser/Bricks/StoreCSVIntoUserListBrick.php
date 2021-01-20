<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class StoreCSVIntoUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STORE_CSV_INTO_USERLIST_BRICK;
    $this->caption = 'Store CSV column into list';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
