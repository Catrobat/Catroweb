<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ReplaceItemInUserListBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::REPLACE_ITEM_LIST_BRICK;
    $this->caption = 'Replace item in list _ at position _ with _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
