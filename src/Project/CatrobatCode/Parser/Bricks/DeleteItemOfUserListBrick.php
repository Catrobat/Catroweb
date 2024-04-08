<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class DeleteItemOfUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::DELETE_ITEM_LIST_BRICK;
    $this->caption = 'Delete item from list _ at position _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
