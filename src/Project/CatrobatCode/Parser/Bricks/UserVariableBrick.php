<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class UserVariableBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::USER_VARIABLE_BRICK;
    $this->caption = 'User Variable';
    $this->setImgFile(Constants::DATA_BRICK_IMG);
  }
}
