<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class StoreCSVIntoUserListBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::STORE_CSV_INTO_USERLIST_BRICK;
    $this->caption = 'Store CSV column into list';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
