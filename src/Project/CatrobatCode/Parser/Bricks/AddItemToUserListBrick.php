<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class AddItemToUserListBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::ADD_ITEM_LIST_BRICK;
    $this->caption = 'Add Item to User List';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
