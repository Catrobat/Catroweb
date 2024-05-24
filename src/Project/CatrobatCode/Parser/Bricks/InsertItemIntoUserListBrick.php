<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class InsertItemIntoUserListBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::INSERT_ITEM_LIST_BRICK;
    $this->caption = 'Insert _ into list _ at position _';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
