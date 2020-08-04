<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class InsertItemIntoUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::INSERT_ITEM_LIST_BRICK;
    $this->caption = 'Insert _ into list _ at position _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
