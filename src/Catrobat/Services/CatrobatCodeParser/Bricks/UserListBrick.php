<?php

namespace App\Catrobat\Services\CatrobatCodeParser\Bricks;

use App\Catrobat\Services\CatrobatCodeParser\Constants;

/**
 * Class UserListBrick.
 */
class UserListBrick extends Brick
{
  protected function create()
  {
    $this->type = Constants::USER_LIST_BRICK;
    $this->caption = 'User List';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
