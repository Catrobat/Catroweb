<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class AddItemToUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::ADD_ITEM_LIST_BRICK;
    $this->caption = 'Add Item to User List';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
