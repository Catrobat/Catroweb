<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class AddItemToUserListBrick.
 */
class AddItemToUserListBrick extends Brick
{
  /**
   * @return mixed|void
   */
  protected function create()
  {
    $this->type = Constants::ADD_ITEM_LIST_BRICK;
    $this->caption = 'Add Item to User List';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
