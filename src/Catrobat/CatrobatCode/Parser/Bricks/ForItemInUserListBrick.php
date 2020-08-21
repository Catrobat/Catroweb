<?php

namespace App\Catrobat\CatrobatCode\Parser\Bricks;

use App\Catrobat\CatrobatCode\Parser\Constants;

class ForItemInUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::FOR_ITEM_IN_USER_LIST_BRICK;
    $this->caption = 'For Item In User List Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
