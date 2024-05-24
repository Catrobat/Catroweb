<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class UserListBrick extends Brick
{
  #[\Override]
  protected function create(): void
  {
    $this->type = Constants::USER_LIST_BRICK;
    $this->caption = 'User List';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
