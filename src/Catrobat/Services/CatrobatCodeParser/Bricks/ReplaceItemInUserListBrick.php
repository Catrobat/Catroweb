<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class ReplaceItemInUserListBrick.
 */
class ReplaceItemInUserListBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::REPLACE_ITEM_LIST_BRICK;
    $this->caption = 'Replace item in list _ at position _ with _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
