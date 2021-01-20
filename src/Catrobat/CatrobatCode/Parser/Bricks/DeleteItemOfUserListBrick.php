<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class DeleteItemOfUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::DELETE_ITEM_LIST_BRICK;
    $this->caption = 'Delete item from list _ at position _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
