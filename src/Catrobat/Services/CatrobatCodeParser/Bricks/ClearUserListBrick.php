<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class AddItemToUserListBrick.
 */
class ClearUserListBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::CLEAR_LIST_BRICK;
    $this->caption = 'Clear User List';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
