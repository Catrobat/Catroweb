<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ForItemInUserListBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::FOR_ITEM_IN_USER_LIST_BRICK;
    $this->caption = 'For Item In User List Brick';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
