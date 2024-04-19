<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser\Bricks;

use App\Project\CatrobatCode\Parser\Constants;

class UserDefinedBrick extends Brick
{
  protected function create(): void
  {
    $this->type = Constants::USER_DEFINED_BRICK;
    $this->caption = 'Your Brick';
    $this->setImgFile(Constants::YOUR_BRICK_IMG);
  }
}
