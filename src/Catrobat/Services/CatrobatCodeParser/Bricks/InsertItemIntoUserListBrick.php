<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class InsertItemIntoUserListBrick.
 */
class InsertItemIntoUserListBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::INSERT_ITEM_LIST_BRICK;
    $this->caption = 'Insert _ into list _ at position _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
