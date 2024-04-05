<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class ClearUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::CLEAR_LIST_BRICK;
    $this->caption = 'Clear User List';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
